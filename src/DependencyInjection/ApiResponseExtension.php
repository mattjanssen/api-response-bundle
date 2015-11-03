<?php

namespace MattJanssen\ApiResponseBundle\DependencyInjection;

use MattJanssen\ApiResponseBundle\Serializer\Adapter\JmsSerializerAdapter;
use MattJanssen\ApiResponseBundle\Serializer\Adapter\JsonEncodeSerializerAdapter;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

/**
 * API Response Bundle Kernel Extension
 *
 * @author Matt Janssen <matt@mattjanssen.com>
 */
class ApiResponseExtension extends ConfigurableExtension
{
    /**
     * {@inheritdoc}
     */
    public function loadInternal(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        switch ($mergedConfig['serializer']) {
            case Configuration::SERIALIZER_JSON_ENCODE:
                $definition = (new Definition())
                    ->setClass('MattJanssen\ApiResponseBundle\Serializer\Adapter\JsonEncodeSerializerAdapter');
                break;

            case Configuration::SERIALIZER_JMS_SERIALIZER:
                $definition = (new Definition())
                    ->setClass('MattJanssen\ApiResponseBundle\Serializer\Adapter\JmsSerializerAdapter')
                    ->addArgument(new Reference('jms_serializer'));
                break;

            case Configuration::SERIALIZER_FRACTAL:
                throw new \Exception('Fractal serializer not implemented.');
                break;

            default:
                throw new \Exception('Unrecognized serializer configured.');
        }

        $container->setDefinition('api_response.serializer_adapter', $definition);
    }
}
