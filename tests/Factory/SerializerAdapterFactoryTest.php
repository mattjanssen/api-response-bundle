<?php

namespace MattJanssen\ApiResponseBundle\Test\Factory;

use JMS\Serializer\SerializerBuilder;
use MattJanssen\ApiResponseBundle\DependencyInjection\Configuration;
use MattJanssen\ApiResponseBundle\Factory\SerializerAdapterFactory;
use MattJanssen\ApiResponseBundle\Serializer\Adapter\JmsSerializerAdapter;
use MattJanssen\ApiResponseBundle\Serializer\Adapter\JsonEncodeSerializerAdapter;
use MattJanssen\ApiResponseBundle\Serializer\Adapter\JsonGroupEncodeSerializerAdapter;
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
        $containerMock = \Mockery::mock(Container::class)->shouldReceive('get')->once()->with('jms_serializer')->andReturn(AppMocker::getJmsSerializer())->getMock();
        $adapterFactory = new SerializerAdapterFactory($containerMock);

        $returnedAdaptor = $adapterFactory->createSerializerAdapter(Configuration::SERIALIZER_JMS_SERIALIZER);

        self::assertInstanceOf(JmsSerializerAdapter::class, $returnedAdaptor);
    }

    public function testUnrecognized()
    {
        $adapterFactory = new SerializerAdapterFactory(AppMocker::getUnusedContainer());

        $this->setExpectedException(\RuntimeException::class);

        $adapterFactory->createSerializerAdapter('AN_UNSUPPORTED_ADAPTER');
    }
}
