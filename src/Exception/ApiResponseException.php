<?php

namespace MattJanssen\ApiResponseBundle\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Exception for a Failed API Response
 *
 * When thrown, the code and message and data from this exception will be placed into a failed API response.
 *
 * Note: It is recommended that this exception only be thrown from the controller actions themselves.
 *
 * @author Matt Janssen <matt@mattjanssen.com>
 */
class ApiResponseException extends \Exception implements ApiResponseExceptionInterface
{
    /**
     * HTTP Response Status Code
     *
     * @var int
     */
    private $httpStatusCode;

    /**
     * Extra Error Data to Expose to Client
     *
     * Use this to add data objects related to the error.
     *
     * An example usage would be to include a Symfony Form object after errors were encountered, but only
     * if you have already setup the serializer to handle Symfony Form objects.
     *
     * Note: Do not use this to send stack traces or other sensitive data as it may be serialized to the client.
     *
     * @var mixed
     */
    private $errorData;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        $message = null,
        $code = 0,
        $httpCode = Response::HTTP_BAD_REQUEST,
        $errorData = null,
        \Exception $previous = null
    ) {
        if (null === $message) {
            // By default, use the corresponding generic HTTP status message as the API error message.
            $message = Response::$statusTexts[$httpCode];
        }

        parent::__construct($message, $code, $previous);

        $this->setHttpStatusCode($httpCode);
        $this->setErrorData($errorData);
    }

    /**
     * {@inheritdoc}
     */
    public function getHttpStatusCode()
    {
        return $this->httpStatusCode;
    }

    /**
     * {@inheritdoc}
     */
    public function setHttpStatusCode($httpStatusCode)
    {
        $this->httpStatusCode = $httpStatusCode;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getErrorData()
    {
        return $this->errorData;
    }

    /**
     * {@inheritdoc}
     */
    public function setErrorData($errorData)
    {
        $this->errorData = $errorData;
    }
}
