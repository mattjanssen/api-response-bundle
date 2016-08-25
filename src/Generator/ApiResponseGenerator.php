<?php

namespace MattJanssen\ApiResponseBundle\Generator;

use MattJanssen\ApiResponseBundle\DependencyInjection\Configuration;
use MattJanssen\ApiResponseBundle\Factory\SerializerAdapterFactory;
use MattJanssen\ApiResponseBundle\Model\ApiResponseErrorModel;
use MattJanssen\ApiResponseBundle\Model\ApiResponseResponseModel;
use Symfony\Component\HttpFoundation\Response;

/**
 * API Response Generator
 *
 * @author Matt Janssen <matt@mattjanssen.com>
 */
class ApiResponseGenerator
{
    /**
     * Serializer Adapter Factory
     *
     * @var SerializerAdapterFactory
     */
    private $serializerAdapterFactory;

    /**
     * Constructor
     *
     * @param SerializerAdapterFactory $serializerAdapterFactory
     */
    public function __construct(
        SerializerAdapterFactory $serializerAdapterFactory
    ) {
        $this->serializerAdapterFactory = $serializerAdapterFactory;
    }

    /**
     * Convert Data Into a Successful API Response
     *
     * Defaults to json_encode serializer.
     *
     * @param mixed $data
     * @param int $httpCode
     * @param array $serializeGroups
     * @param string|null $serializerName Name of serializer to use instead of the default.
     *
     * @return Response
     * @throws \Exception
     */
    public function generateSuccessResponse(
        $data = null,
        $httpCode = null,
        array $serializeGroups = null,
        $serializerName = null
    ) {
        // Set defaults.
        if (null === $httpCode) {
            $httpCode = Response::HTTP_OK;
        }

        if (null === $serializeGroups) {
            $serializeGroups = [];
        }

        if (null === $serializerName) {
            $serializerName = Configuration::SERIALIZER_JSON_ENCODE;
        }

        $serializerAdapter = $this->serializerAdapterFactory->createSerializerAdapter($serializerName);

        $apiResponseModel = (new ApiResponseResponseModel())
            ->setData($data);

        $jsonString = $serializerAdapter->serialize($apiResponseModel, $serializeGroups);

        $response = new Response($jsonString, $httpCode, ['Content-Type' => 'application/json']);

        return $response;
    }

    /**
     * Create a Failed API Response
     *
     * This assumes the json_encode serializer.
     *
     * @param int $httpCode
     * @param int $errorCode
     * @param string|null $errorTitle
     * @param mixed|null $errorData
     *
     * @return Response
     */
    public function generateErrorResponse(
        $httpCode = null,
        $errorCode = null,
        $errorTitle = null,
        $errorData = null
    ) {
        // Set defaults.
        if (null === $httpCode) {
            $httpCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        if (null === $errorCode) {
            $errorCode = 0;
        }

        $apiErrorModel = (new ApiResponseErrorModel())
            ->setCode($errorCode)
            ->setTitle($errorTitle)
            ->setErrorData($errorData);

        $apiResponseModel = (new ApiResponseResponseModel())
            ->addError($apiErrorModel);

        $serializerAdapter = $this->serializerAdapterFactory->createSerializerAdapter(Configuration::SERIALIZER_JSON_ENCODE);

        $jsonString = $serializerAdapter->serialize($apiResponseModel);

        $response = new Response($jsonString, $httpCode, ['Content-Type' => 'application/json']);

        return $response;
    }
}
