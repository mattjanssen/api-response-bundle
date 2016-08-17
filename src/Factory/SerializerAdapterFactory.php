<?php

namespace MattJanssen\ApiResponseBundle\Factory;

use MattJanssen\ApiResponseBundle\DependencyInjection\Configuration;
use MattJanssen\ApiResponseBundle\Serializer\Adapter as Adapter;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Factory for Instantiating Serializer Adapters
 *
 * @author Matt Janssen <matt@mattjanssen.com>
 */
class SerializerAdapterFactory
{
    /**
     * DI Container
     *
     * Used to build the appropriate serialization adapter at runtime depending on configuration and annotation.
     *
     * Note: The serializer isn't known until the response has completed, so we cannot just inject a specific serializer
     * from the container upon boot up. We also can't inject all serializers, because not every application will include
     * all serializers.
     *
     * @var ContainerInterface
     */
    private $container;

    /**
     * Constructor
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Instantiate the Requested Serializer Adapter
     *
     * @param string $serializerName Name of serializer to use (from constants).
     *
     * @return Adapter\SerializerAdapterInterface
     *
     * @throws \Exception
     */
    public function createSerializerAdapter($serializerName)
    {
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

            default:
                throw new \RuntimeException('Unrecognized serializer configured.');
        }

        return $serializerAdapter;
    }
}
