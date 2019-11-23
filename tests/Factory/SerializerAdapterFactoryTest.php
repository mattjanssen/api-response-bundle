<?php

namespace MattJanssen\ApiResponseBundle\Test\Factory;

use MattJanssen\ApiResponseBundle\DependencyInjection\Configuration;
use MattJanssen\ApiResponseBundle\Factory\SerializerAdapterFactory;
use MattJanssen\ApiResponseBundle\Serializer\Adapter\JmsSerializerAdapter;
use MattJanssen\ApiResponseBundle\Serializer\Adapter\JsonEncodeSerializerAdapter;
use MattJanssen\ApiResponseBundle\Serializer\Adapter\JsonGroupEncodeSerializerAdapter;
use MattJanssen\ApiResponseBundle\Serializer\Adapter\SerializerAdapterInterface;
use MattJanssen\ApiResponseBundle\Test\AppMocker;
use MattJanssen\ApiResponseBundle\Test\AppTestCase;
use Symfony\Component\DependencyInjection\Container;

class SerializerAdapterFactoryTest extends AppTestCase
{
    public function testJson()
    {
        $adapterFactory = new SerializerAdapterFactory(AppMocker::getUnusedContainer());

        $returnedAdaptor = $adapterFactory->createSerializerAdapter(Configuration::SERIALIZER_JSON_ENCODE);

        self::assertInstanceOf(JsonEncodeSerializerAdapter::class, $returnedAdaptor);
    }

    public function testJsonGroup()
    {
        $adapterFactory = new SerializerAdapterFactory(AppMocker::getUnusedContainer());

        $returnedAdaptor = $adapterFactory->createSerializerAdapter(Configuration::SERIALIZER_JSON_GROUP_ENCODE);

        self::assertInstanceOf(JsonGroupEncodeSerializerAdapter::class, $returnedAdaptor);
    }

    public function testJms()
    {
        /** @var \Symfony\Component\DependencyInjection\ContainerInterface $containerMock */
        $containerMock = \Mockery::mock(Container::class)->shouldReceive('get')->once()->with('jms_serializer')->andReturn(AppMocker::getJmsSerializer())->getMock();
        $adapterFactory = new SerializerAdapterFactory($containerMock);

        $returnedAdaptor = $adapterFactory->createSerializerAdapter(Configuration::SERIALIZER_JMS_SERIALIZER);

        self::assertInstanceOf(JmsSerializerAdapter::class, $returnedAdaptor);
    }

    public function testCustomService()
    {
        $customAdapter = \Mockery::mock(SerializerAdapterInterface::class);

        $container = \Mockery::mock(Container::class)->shouldReceive('has')->once()->with('CUSTOM_ADAPTER')->andReturn(true)
            ->shouldReceive('get')->once()->with('CUSTOM_ADAPTER')->andReturn($customAdapter)->getMock();
        $adapterFactory = new SerializerAdapterFactory($container);
        $returnedAdaptor = $adapterFactory->createSerializerAdapter('CUSTOM_ADAPTER');

        self::assertInstanceOf(SerializerAdapterInterface::class, $returnedAdaptor);
    }

    public function testUnrecognized()
    {
        $container = \Mockery::mock(Container::class)->shouldReceive('has')->once()->with('AN_UNSUPPORTED_ADAPTER')->andReturn(false)->getMock();
        $adapterFactory = new SerializerAdapterFactory($container);

        $this->expectException(\RuntimeException::class);

        $adapterFactory->createSerializerAdapter('AN_UNSUPPORTED_ADAPTER');
    }
}
