<?php

namespace MattJanssen\ApiResponseBundle\Test;

use JMS\Serializer\SerializerBuilder;
use MattJanssen\ApiResponseBundle\DependencyInjection\Configuration;
use MattJanssen\ApiResponseBundle\Factory\SerializerAdapterFactory;
use MattJanssen\ApiResponseBundle\Generator\ApiResponseGenerator;
use Symfony\Component\DependencyInjection\Container;

class AppMocker
{
    public static function getUnusedContainer()
    {
        return \Mockery::mock(Container::class);
    }

    public static function getJmsSerializer()
    {
        return SerializerBuilder::create()->build();
    }

    public static function getContainerForFactory()
    {
        $containerMock = new Container();
        $containerMock->set('jms_serializer', self::getJmsSerializer());

        return $containerMock;
    }

    public static function getAdapterFactory()
    {
        return new SerializerAdapterFactory(self::getContainerForFactory());
    }

    public static function getApiResponseGenerator($serializer = Configuration::SERIALIZER_JSON_ENCODE)
    {
        return  new ApiResponseGenerator(self::getAdapterFactory(), $serializer);
    }
}
