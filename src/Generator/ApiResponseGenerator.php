<?php

namespace MattJanssen\ApiResponseBundle\Generator;

use MattJanssen\ApiResponseBundle\Compiler\ApiConfigCompiler;
use MattJanssen\ApiResponseBundle\DependencyInjection\Configuration;
use MattJanssen\ApiResponseBundle\Factory\SerializerAdapterFactory;
use MattJanssen\ApiResponseBundle\Model\ApiResponseErrorModel;
use MattJanssen\ApiResponseBundle\Model\ApiResponseResponseModel;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * API Response Generator
 *
 * @author Matt Janssen <matt@mattjanssen.com>
 */
class ApiResponseGenerator
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var ApiConfigCompiler
     */
    private $configCompiler;

    /**
     * Serializer Adapter Factory
     *
     * @var SerializerAdapterFactory
     */
    private $serializerAdapterFactory;

    /**
     * Constructor
     *
     * @param RequestStack $requestStack
     * @param ApiConfigCompiler $configCompiler
     * @param SerializerAdapterFactory $serializerAdapterFactory
     */
    public function __construct(
        RequestStack $requestStack,
        ApiConfigCompiler $configCompiler,
        SerializerAdapterFactory $serializerAdapterFactory
    ) {
        $this->requestStack = $requestStack;
        $this->configCompiler = $configCompiler;
        $this->serializerAdapterFactory = $serializerAdapterFactory;
    }

    /**
     * Convert Data Into a Successful API Response
     *
     * Defaults to json_encode serializer.
     *
     * @param mixed $data
     * @param int|null $httpCode
     * @param string[]|null $serializeGroups
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
        if ($httpCode === null) {
            $httpCode = Response::HTTP_OK;
        }

        $request = $this->requestStack->getMasterRequest();
        $pathConfig = $request ? $this->configCompiler->compileApiConfig($request) : null;

        if ($serializeGroups === null && $pathConfig !== null) {
            $serializeGroups = $pathConfig->getGroups();
        }

        if ($serializerName === null) {
            if  ($pathConfig !== null) {
                $serializerName = $pathConfig->getSerializer();
            } else {
                $serializerName = Configuration::SERIALIZER_JSON_ENCODE;
            }
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
     * @param string $errorCode
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
