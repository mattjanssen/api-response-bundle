<?php

namespace ApiWrapBundle\DependencyInjection;

use ApiWrapBundle\Serializer\Adapter\JmsSerializerAdapter;
use ApiWrapBundle\Serializer\Adapter\JsonEncodeSerializerAdapter;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;

/**
 * API Wrap Bundle Kernel Extension
 *
 * @author Matt Janssen <matt@mattjanssen.com>
 */
class ApiWrapExtension extends ConfigurableExtension
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
                    ->setClass(JsonEncodeSerializerAdapter::class);
                break;

            case Configuration::SERIALIZER_JMS_SERIALIZER:
                $definition = (new Definition())
                    ->setClass(JmsSerializerAdapter::class)
                    ->addArgument(new Reference('jms_serializer'));
                break;

            case Configuration::SERIALIZER_FRACTAL:
                throw new \Exception('Fractal serializer not implemented.');
                break;

            default:
                throw new \Exception('Unrecognized serializer configured.');
        }

        $container->setDefinition('api_wrap.serializer_adapter', $definition);
    }
}
