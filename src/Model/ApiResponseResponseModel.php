<?php

namespace MattJanssen\ApiResponseBundle\Model;

use MattJanssen\ApiResponseBundle\Serializer\ArraySerializable;

/**
 * API Response Response Model
 *
 * This is the base object that is finally serialized into a Symfony Response.
 * It includes the data object and error object, both of which are optional.
 *
 * @author Matt Janssen <matt@mattjanssen.com>
 */
class ApiResponseResponseModel implements \JsonSerializable, ArraySerializable
{
    /**
     * Serializable API Response Data
     *
     * @var mixed
     */
    private $data;

    /**
     * API Error Model
     *
     * @var ApiResponseErrorModel
     */
    private $error;

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return [
            'data' => $this->data,
            'error' => $this->error,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function arraySerialize(array $group = [])
    {
        return $this->jsonSerialize();
    }

    /**
     * Get the API Response Data
     *
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set the API Response Data
     *
     * @param mixed $data
     *
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get the API Error Model
     *
     * @return ApiResponseErrorModel
     */
    public function getErrors()
    {
        return $this->error;
    }

    /**
     * Set the API Error Model
     *
     * @param ApiResponseErrorModel $error
     *
     * @return $this
     */
    public function addError(ApiResponseErrorModel $error)
    {
        $this->error = $error;

        return $this;
    }
}
