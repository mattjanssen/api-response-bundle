<?php

namespace MattJanssen\ApiResponseBundle\Subscriber;

use MattJanssen\ApiResponseBundle\Annotation\ApiResponse;
use MattJanssen\ApiResponseBundle\Exception\ApiResponseExceptionInterface;
use MattJanssen\ApiResponseBundle\Generator\ApiResponseGenerator;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
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
     * Kernel's Debug Status
     *
     * @var bool
     */
    private $debug;

    /**
     * Constructor
     *
     * @param ApiResponseGenerator $responseGenerator
     * @param bool $debug
     */
    public function __construct(
        ApiResponseGenerator $responseGenerator,
        $debug
    ) {
        $this->responseGenerator = $responseGenerator;
        $this->debug = $debug;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::VIEW => 'onKernelView',
            KernelEvents::EXCEPTION => ['onKernelException', 100],
        ];
    }

    /**
     * Convert Returned Controller Data into a successful API Response
     *
     * This only performs if the @ApiResponse annotation was used on the controller or action.
     *
     * @param GetResponseForControllerResultEvent $event
     *
     * @throws \Exception
     */
    public function onKernelView(GetResponseForControllerResultEvent $event)
    {
        $request = $event->getRequest();

        /** @var ApiResponse $annotation */
        $annotation = $request->attributes->get('_' . ApiResponse::ALIAS_NAME);
        if (!$annotation) {
            // The annotation was not present on this controller/action.
            return;
        }

        $data = $event->getControllerResult();

        $response = $this->responseGenerator->generateSuccessResponse(
            $data,
            $annotation->getHttpCode(),
            $annotation->getGroups(),
            $annotation->getSerializer()
        );

        $event->setResponse($response);
    }

    /**
     * Create a Failed API Response from an Exception
     *
     * This only performs if the @ApiResponse annotation was used on the controller or action.
     *
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $request = $event->getRequest();

        /** @var ApiResponse $annotation */
        $annotation = $request->attributes->get('_' . ApiResponse::ALIAS_NAME);
        if (!$annotation) {
            // The annotation was not present on this controller/action.
            return;
        }

        $exception = $event->getException();

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
}
