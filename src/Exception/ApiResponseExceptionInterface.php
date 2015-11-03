<?php

namespace MattJanssen\ApiResponseBundle\Exception;

/**
 * Exception Interface for a Failed API Response
 *
 * When thrown, the code and message and data from this interface will be placed into a failed API response.
 *
 * Note: It is recommended that this exception only be thrown from the controller actions themselves.
 *
 * @author Matt Janssen <matt@mattjanssen.com>
 */
interface ApiResponseExceptionInterface
{
    /**
     * Get the HTTP Status Code
     *
     * @return int
     */
    public function getHttpStatusCode();

    /**
     * Set the HTTP Status Code
     *
     * @param int $httpStatusCode
     *
     * @return $this
     */
    public function setHttpStatusCode($httpStatusCode);

    /**
     * Get Extra Data to Expose to Client
     *
     * @return mixed
     */
    public function getErrorData();

    /**
     * Set Extra Data to Expose to Client
     *
     * @param $errorData
     *
     * @return mixed
     */
    public function setErrorData($errorData);
}
