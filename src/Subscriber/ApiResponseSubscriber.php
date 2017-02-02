<?php

namespace MattJanssen\ApiResponseBundle\Subscriber;

use MattJanssen\ApiResponseBundle\Annotation\ApiResponse;
use MattJanssen\ApiResponseBundle\Compiler\ApiConfigCompiler;
use MattJanssen\ApiResponseBundle\Exception\ApiResponseExceptionInterface;
use MattJanssen\ApiResponseBundle\Generator\ApiResponseGenerator;
use Psr\Log\LoggerInterface;
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
     * API Configuration Compiler
     *
     * @var ApiConfigCompiler
     */
    private $configCompiler;

    /**
     * PSR Logger
     *
     * @var LoggerInterface
     */
    private $logger;

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
     * @param ApiConfigCompiler $configCompiler
     * @param LoggerInterface $logger
     * @param bool $debug
     */
    public function __construct(
        ApiResponseGenerator $responseGenerator,
        ApiConfigCompiler $configCompiler,
        LoggerInterface $logger,
        $debug
    ) {
        $this->responseGenerator = $responseGenerator;
        $this->configCompiler = $configCompiler;
        $this->logger = $logger;
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
     * Convert Returned Controller Data into a successful API Response
     *
     * @param GetResponseForControllerResultEvent $event
     *
     * @throws \Exception
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $request = $event->getRequest();
        $data = $event->getControllerResult();

        $pathConfig = $this->configCompiler->compileApiConfig($request);
        if (null === $pathConfig) {
            // This is not an API endpoint.
            return;
        }

        /** @var ApiResponse $annotation */
        $annotation = $request->attributes->get('_' . ApiResponse::ALIAS_NAME);

        $response = $this->responseGenerator->generateSuccessResponse(
            $data,
            $annotation ? $annotation->getHttpCode() : null,
            $pathConfig->getGroups(),
            $pathConfig->getSerializer()
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

        $pathConfig = $this->configCompiler->compileApiConfig($request);
        if (null === $pathConfig) {
            // This is not an API endpoint.
            return;
        }

        $exception = $event->getException();

        // Check if this is an OPTIONS request.
        if ($exception instanceof MethodNotAllowedHttpException && $request->getMethod() === Request::METHOD_OPTIONS) {
            // A MethodNotAllowedHttpException implies that the route exists but not for the requested HTTP method.
            // In the case of a an OPTIONS request (CORS preflight) we send a 200 OK instead of a 404 Not Found.
            $response = $this->responseGenerator->generateSuccessResponse();

            // Explicitly set the 200 OK status code as the X-Status-Code header so the HttpKernel allows the 200.
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

        if ($httpCode >= Response::HTTP_INTERNAL_SERVER_ERROR) {
            // Log exceptions that result in a 5xx server response.
            $this->logger->critical(
                sprintf('API Exception %s: "%s" at %s line %s', get_class($exception), $exception->getMessage(), $exception->getFile(), $exception->getLine()),
                ['exception' => $exception]
            );
        }
    }

    /**
     * Modify Header on all API Responses
     *
     * This takes place after onKernelException and onKernelView events.
     * It takes care of any WWW-Authenticate and CORS headers for routes/actions that handle API requests.
     *
     * @param FilterResponseEvent $event
     *
     * @throws \Exception
     */
    public function onKernelResponse(FilterResponseEvent $event)
    {
        $request = $event->getRequest();
        $response = $event->getResponse();

        $pathConfig = $this->configCompiler->compileApiConfig($request);
        if (null === $pathConfig) {
            // This is not an API response.
            return;
        }

        // Do not allow caching of API responses. This may be enhanced to be configurable in the future.
        $response->headers->addCacheControlDirective('no-cache');
        $response->headers->addCacheControlDirective('no-store');
        $response->headers->addCacheControlDirective('max-age', '0');
        $response->headers->addCacheControlDirective('must-revalidate');
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', 'Mon, 01 Jan 1990 00:00:00 GMT');

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
