<?php

namespace MattJanssen\ApiResponseBundle\Subscriber;

use MattJanssen\ApiResponseBundle\Annotation\ApiResponse;
use MattJanssen\ApiResponseBundle\Exception\ApiResponseExceptionInterface;
use MattJanssen\ApiResponseBundle\Generator\ApiResponseGenerator;
use MattJanssen\ApiResponseBundle\Model\ApiPathConfig;
use MattJanssen\ApiResponseBundle\Model\ApiPathConfigInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

/**
 * Subscriber of Kernel Events to Generate an API Response
 *
 * When a non-Response object is returned from a controller action,
 * this subscriber checks for the @ApiResponse annotation and serializes the response.
 *
 * This subscriber also handles exceptions thrown from controller actions with the @ApiResponse annotation.
 *
 * @author Matt Janssen <matt@mattjanssen.com>
 */
class ApiResponseSubscriber implements EventSubscriberInterface
{
    /**
     * API Response Generator
     *
     * @var ApiResponseGenerator
     */
    private $responseGenerator;

    /**
     * API Path Configurations
     *
     * Each relative URL path has its own CORS configuration settings in this array.
     *
     * @var ApiPathConfig[]
     */
    private $pathConfigs;

    /**
     * Kernel's Debug Status
     *
     * @var bool
     */
    private $debug;

    /**
     * Constructor
     *
     * @param ApiResponseGenerator $responseGenerator
     * @param array $pathConfigs
     * @param bool $debug
     */
    public function __construct(
        ApiResponseGenerator $responseGenerator,
        $pathConfigs,
        $debug
    ) {
        $this->responseGenerator = $responseGenerator;
        $this->pathConfigs = $pathConfigs;
        $this->debug = $debug;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE => ['onKernelResponse', 4096],
            KernelEvents::VIEW => ['onKernelView', 4096],
            KernelEvents::EXCEPTION => ['onKernelException', 4096],
        ];
    }

    /**
     * @param Request $request
     *
     * @return ApiPathConfigInterface
     */
    private function getPathConfig(Request $request)
    {
        // This boolean is flipped only if the request matches a path as specified in config.yml,
        // or if its controller action has the @ApiResponse() annotation.
        $pathServed = false;

        $compiledConfig = new ApiPathConfig();

        $originPath = $request->getPathInfo();

        // Create the ApiPathConfig based on the per-path options in config.yml.
        foreach ($this->pathConfigs as $pathRegex => $pathConfig) {
            if (!$pathConfig->isOriginAllowed($originPath)) {
                continue;
            }

            $pathServed = true;

            // Override defaults with any config.yml sepcifications.
            if (null !== $pathConfig['cors_allow_origin_regex']) {
                $compiledConfig->setCorsAllowOriginRegex($pathConfig['cors_allow_origin_regex']);
            }
            if (null !== $pathConfig['cors_allow_headers']) {
                $compiledConfig->setCorsAllowHeaders($pathConfig['cors_allow_headers']);
            }
            if (null !== $pathConfig['cors_max_age']) {
                $compiledConfig->setCorsMaxAge($pathConfig['cors_max_age']);
            }

            // After the first path match, don't process the rest.
            break;
        }

        /** @var ApiResponse $attribute */
        $attribute = $request->attributes->get('_' . ApiResponse::ALIAS_NAME);

        // Override the ApiPathConfig properties with any optional @ApiResponse() annotation settings.
        if (null !== $attribute) {
            $pathServed = true;

            // Override config.yml settings with any annotation specifications.
            if (null !== $attribute->getCorsAllowOriginRegex()) {
                $compiledConfig->setCorsAllowOriginRegex($attribute->getCorsAllowOriginRegex());
            }
            if (null !== $attribute->getCorsAllowHeaders()) {
                $compiledConfig->setCorsAllowHeaders($attribute->getCorsAllowHeaders());
            }
            if (null !== $attribute->getCorsMaxAge()) {
                $compiledConfig->setCorsMaxAge($attribute->getCorsMaxAge());
            }
        }

        if ($pathServed) {
            return $compiledConfig;
        }

        return null;
    }

    /**
     * Merge Non-null Options from a Config into Another Config
     *
     * @param ApiPathConfig $compiledConfig
     * @param ApiPathConfig $configToMerge
     */
    public function mergeConfig($compiledConfig, $configToMerge)
    {
        if (null !== $configToMerge->getCorsAllowOriginRegex()) {
            $compiledConfig->setCorsAllowOriginRegex($configToMerge->getCorsAllowOriginRegex());
        }
        if (null !== $configToMerge->getCorsAllowHeaders()) {
            $compiledConfig->setCorsAllowHeaders($configToMerge->getCorsAllowHeaders());
        }
        if (null !== $configToMerge->getCorsMaxAge()) {
            $compiledConfig->setCorsMaxAge($configToMerge->getCorsMaxAge());
        }
    }

    /**
     * Convert Returned Controller Data into a successful API Response
     *
     * @param GetResponseForControllerResultEvent $event
     *
     * @throws \Exception
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $request = $event->getRequest();

        $pathConfig = $this->getPathConfig($request);
        if (null === $pathConfig) {
            return;
        }

        /** @var ApiResponse $annotation */
        $annotation = $request->attributes->get('_' . ApiResponse::ALIAS_NAME);

        $data = $event->getControllerResult();

        $response = $this->responseGenerator->generateSuccessResponse(
            $data,
            $annotation ? $annotation->getHttpCode() : Response::HTTP_OK,
            $annotation ? $annotation->getGroups() : [],
            $annotation ? $annotation->getSerializer() : null
        );

        $event->setResponse($response);
    }

    /**
     * Create a Failed API Response from an Exception
     *
     * The HttpKernel exception catcher tries to enforce an error HTTP response code.
     * This can be avoided by using the X-Status-Code header.
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $request = $event->getRequest();

        $pathConfig = $this->getPathConfig($request);
        if (null === $pathConfig) {
            return;
        }

        $exception = $event->getException();

        if ($exception instanceof MethodNotAllowedHttpException && $request->getMethod() === Request::METHOD_OPTIONS) {
            // A MethodNotAllowedHttpException implies that the route exists but not for the requested HTTP method.
            // In the case of a an OPTIONS request (CORS preflight) we send a 200 OK instead of a 404 Not Found.
            $response = $this->responseGenerator->generateSuccessResponse([]);

            // Explicitly set the 200 OK status code as a specical header that the HttpKernel doesn't change it.
            /** @see Symfony\Component\HttpKernel\HttpKernel::handleException() */
            $response->headers->set('X-Status-Code', Response::HTTP_OK);

            // The MethodNotAllowedHttpException exception is populated with the methods that do exist for a route.
            // Use these existing methods in the Allow header.
            $exceptionHeaders = $exception->getHeaders();
            $response->headers->set('Allow', isset($exceptionHeaders['Allow']) ? $exceptionHeaders['Allow'] : null);

            $event->setResponse($response);

            return;
        }

        $httpCode = null;
        $errorCode = null;
        $errorTitle = null;
        $errorData = null;

        // Determine the API error code, API error title, and HTTP status code
        // depending on the type of exception thrown.
        if ($exception instanceof ApiResponseExceptionInterface) {
            // There is a separate HTTP status code that can also be set on the exception. The default is 400.
            $httpCode = $exception->getHttpStatusCode();
            $errorCode = $exception->getCode();
            $errorTitle = $exception->getMessage();
            $errorData = $exception->getErrorData();
        } elseif ($exception instanceof HttpExceptionInterface) {
            // Use the code from the Symfony HTTP exception as both the API error code and the HTTP status code.
            $httpCode = $exception->getStatusCode();
            $errorCode = $exception->getStatusCode();
            $errorTitle = Response::$statusTexts[$exception->getStatusCode()];
        } elseif ($exception instanceof AuthenticationException) {
            // Authentication exceptions use 401 for both the API error code and the HTTP status code.
            $httpCode = Response::HTTP_UNAUTHORIZED;
            $errorCode = Response::HTTP_UNAUTHORIZED;
            $errorTitle = Response::$statusTexts[Response::HTTP_UNAUTHORIZED];
        } elseif ($exception instanceof AccessDeniedException) {
            // Authorization exceptions use 403 for both the API error code and the HTTP status code.
            $httpCode = Response::HTTP_FORBIDDEN;
            $errorCode = Response::HTTP_FORBIDDEN;
            $errorTitle = Response::$statusTexts[Response::HTTP_FORBIDDEN];
        } else {
            // All other errors use 500 for both the API error code and the HTTP status code.
            $httpCode = Response::HTTP_INTERNAL_SERVER_ERROR;
            $errorCode = Response::HTTP_INTERNAL_SERVER_ERROR;

            // The API error title is determined based on the environment.
            if ($this->debug) {
                // For debug environments, exception messages and trace get passed straight through to the client.
                $message = sprintf(
                    'exception \'%s\' with message \'%s\' in %s:%s',
                    get_class($exception),
                    $exception->getMessage(),
                    $exception->getFile(),
                    $exception->getLine()
                );
                $errorTitle = $message;
                $errorData = $exception->getTraceAsString();
            } else {
                // For non-debug environments, use the corresponding generic HTTP status message as the API error title.
                $errorTitle = Response::$statusTexts[Response::HTTP_INTERNAL_SERVER_ERROR];
            }
        }

        $response = $this->responseGenerator->generateErrorResponse(
            $httpCode,
            $errorCode,
            $errorTitle,
            $errorData
        );

        $event->setResponse($response);
    }

    /**
     * @param FilterResponseEvent $event
     *
     * @throws \Exception
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        $pathConfig = $this->getPathConfig($request);
        if (null === $pathConfig) {
            return;
        }

        // Remove the WWW-Authenticate header that may have been added by the firewall system.
        // If this is sent to a JavaScript API client, the client's browser may pop up an HTTP Basic auth dialog.
        $response->headers->remove('WWW-Authenticate');

        // Add CORS headers as needed.
        $originRegex = $pathConfig->getCorsAllowOriginRegex();
        if (null === $originRegex) {
            // If the allow origin is null (the default) then CORS headers are never added.
            return;
        }

        $requestOrigin = $request->headers->get('Origin');
        if (null === $requestOrigin) {
            // If no origin was specified then this is not a CORS request.
            return;
        }

        if (!preg_match('#' . str_replace('#', '\#', $originRegex) . '#', $requestOrigin)) {
            // If the requesting origin doesn't match the allowed origin regex then no CORS headers are added.
            return;
        }

        $response->headers->set('Access-Control-Allow-Origin', $requestOrigin); // Return the requesting origin.
        $response->headers->set('Access-Control-Allow-Headers', implode(', ', $pathConfig->getCorsAllowHeaders()));
        $response->headers->set('Access-Control-Allow-Methods', $response->headers->get('Allow'));
        $response->headers->set('Access-Control-Max-Age', $pathConfig->getCorsMaxAge());
    }
}
