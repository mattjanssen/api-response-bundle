<?php

namespace MattJanssen\ApiResponseBundle\Exception;

use Symfony\Component\HttpFoundation\Response;

/**
 * Exception for a Failed API Response
 *
 * When thrown, the code and message from this exception will be placed into a failed API response.
 *
 * Note: It is recommended that this exception only be thrown from the controller actions themselves.
 *
 * @author Matt Janssen <matt@mattjanssen.com>
 */
class ApiResponseException extends \Exception
{
    /**
     * HTTP Response Status Code
     *
     * @var int
     */
    private $httpStatusCode;

    /**
     * {@inheritdoc}
     */
    public function __construct(
        $message = null,
        $code = 0,
        $httpCode = Response::HTTP_BAD_REQUEST,
        \Exception $previous = null
    ) {
        if (null === $message) {
            // By default, use the corresponding generic HTTP status message as the API error message.
            $message = Response::$statusTexts[$httpCode];
        }

        parent::__construct($message, $code, $previous);

        $this->setHttpStatusCode($httpCode);
    }

    /**
     * Get the HTTP Status Code
     *
     * @return int
     */
    public function getHttpStatusCode()
    {
        return $this->httpStatusCode;
    }

    /**
     * Set the HTTP Status Code
     *
     * @param int $httpStatusCode
     *
     * @return $this
     */
    public function setHttpStatusCode($httpStatusCode)
    {
        $this->httpStatusCode = $httpStatusCode;

        return $this;
    }
}
