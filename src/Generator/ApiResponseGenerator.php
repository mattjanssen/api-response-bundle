<?php

namespace MattJanssen\ApiResponseBundle\Generator;

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
     * Default Serializer
     *
     * One of the Configuration::SERIALIZER_* constants.
     * @see MattJanssen\ApiResponseBundle\DependencyInjection\Configuration::SERIALIZER_JSON_ENCODE
     *
     * @var string
     */
    private $defaultSerializerName;

    /**
     * Constructor
     *
     * @param SerializerAdapterFactory $serializerAdapterFactory
     * @param string $defaultSerializer
     */
    public function __construct(
        SerializerAdapterFactory $serializerAdapterFactory,
        $defaultSerializer
    ) {
        $this->serializerAdapterFactory = $serializerAdapterFactory;
        $this->defaultSerializerName = $defaultSerializer;
    }

    /**
     * Convert Data Into a Successful API Response
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
        $data,
        $httpCode = Response::HTTP_OK,
        array $serializeGroups = [],
        $serializerName = null
    ) {
        if (null === $serializerName) {
            $serializerName = $this->defaultSerializerName;
        }

        $serializerAdapter = $this->serializerAdapterFactory->createSerializerAdapter($serializerName);

        $apiResponseModel = (new ApiResponseResponseModel())
            ->setData($data);

        $jsonString = $serializerAdapter->serialize($apiResponseModel, $serializeGroups);

        $response = new Response($jsonString, $httpCode, ['Content-Type' => 'application/json']);

        // Do not allow caching of API responses. This may be enhanced to be configurable in the future.
        $response->headers->addCacheControlDirective('no-cache', true);
        $response->headers->addCacheControlDirective('no-store', true);
        $response->headers->addCacheControlDirective('must-revalidate', true);
        $response->headers->set('Pragma', 'no-cache');
        $response->headers->set('Expires', '0');
        
        return $response;
    }

    /**
     * Create a Failed API Response
     *
     * @param int $httpCode
     * @param int $errorCode
     * @param string|null $errorTitle
     * @param mixed|null $errorData
     *
     * @return Response
     */
    public function generateErrorResponse(
        $httpCode = Response::HTTP_INTERNAL_SERVER_ERROR,
        $errorCode = 0,
        $errorTitle = null,
        $errorData = null
    ) {
        $apiErrorModel = (new ApiResponseErrorModel())
            ->setCode($errorCode)
            ->setTitle($errorTitle)
            ->setErrorData($errorData);

        $apiResponseModel = (new ApiResponseResponseModel())
            ->addError($apiErrorModel);

        $serializerAdapter = $this->serializerAdapterFactory->createSerializerAdapter($this->defaultSerializerName);

        $jsonString = $serializerAdapter->serialize($apiResponseModel);

        $response = new Response($jsonString, $httpCode, ['Content-Type' => 'application/json']);

        return $response;
    }
}
