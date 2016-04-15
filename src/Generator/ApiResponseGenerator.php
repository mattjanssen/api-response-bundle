<?php

namespace MattJanssen\ApiResponseBundle\Generator;

use MattJanssen\ApiResponseBundle\DependencyInjection\Configuration;
use MattJanssen\ApiResponseBundle\Model\ApiResponseErrorModel;
use MattJanssen\ApiResponseBundle\Model\ApiResponseResponseModel;
use MattJanssen\ApiResponseBundle\Serializer\Adapter as Adapter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * API Response Generator
 *
 * @author Matt Janssen <matt@mattjanssen.com>
 */
class ApiResponseGenerator
{
    /**
     * DI Container
     *
     * Used to build the appropriate serialization adapter depending on configuration and annotation.
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * Default Serializer
     *
     * One of the Configuration::SERIALIZER_* constants.
     * @see MattJanssen\ApiResponseBundle\DependencyInjection\Configuration::SERIALIZER_JSON_ENCODE
     *
     * @var string
     */
    private $defaultSerializer;

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     * @param string $defaultSerializer
     */
    public function __construct(
        ContainerInterface $container,
        $defaultSerializer
    ) {
        $this->container = $container;
        $this->defaultSerializer = $defaultSerializer;
    }

    /**
     * Convert Data Into a Successful API Response
     *
     * @param mixed $data
     * @param int $httpCode
     * @param array $serializeGroups
     * @param string|null $overrideSerializer Name of serializer to use instead of the default.
     *
     * @return Response
     * @throws \Exception
     */
    public function generateSuccessResponse(
        $data,
        $httpCode = Response::HTTP_OK,
        $serializeGroups = [],
        $overrideSerializer = null
    ) {
        $serializerAdapter = $this->createSerializerAdapter($overrideSerializer);

        $apiResponseModel = (new ApiResponseResponseModel())
            ->setData($data);

        $jsonString = $serializerAdapter->serialize($apiResponseModel, $serializeGroups);

        $response = new Response($jsonString, $httpCode, ['Content-Type' => 'application/json']);

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

        $serializerAdapter = $this->createSerializerAdapter();

        $jsonString = $serializerAdapter->serialize($apiResponseModel);

        $response = new Response($jsonString, $httpCode, ['Content-Type' => 'application/json']);

        return $response;
    }

    /**
     * Instantiate the Requested Serializer Adapter
     *
     * @param string|null $overrideSerializer Name of serializer to use instead of the default.
     *
     * @return Adapter\SerializerAdapterInterface
     *
     * @throws \Exception
     */
    private function createSerializerAdapter($overrideSerializer = null)
    {
        $serializerName = null === $overrideSerializer ? $this->defaultSerializer : $overrideSerializer;

        switch ($serializerName) {
            case Configuration::SERIALIZER_JSON_ENCODE:
                $serializerAdapter = new Adapter\JsonEncodeSerializerAdapter();
                break;

            case Configuration::SERIALIZER_JSON_GROUP_ENCODE:
                $serializerAdapter = new Adapter\JsonGroupEncodeSerializerAdapter();
                break;

            case Configuration::SERIALIZER_JMS_SERIALIZER:
                $jmsSerializer = $this->container->get('jms_serializer');
                $serializerAdapter = new Adapter\JmsSerializerAdapter($jmsSerializer);
                break;

            case Configuration::SERIALIZER_FRACTAL:
                throw new \Exception('Fractal serializer not yet implemented.');
                break;

            default:
                throw new \Exception('Unrecognized serializer configured.');
        }

        return $serializerAdapter;
    }
}
