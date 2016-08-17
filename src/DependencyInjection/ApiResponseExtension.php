<?php

namespace MattJanssen\ApiResponseBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
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

        $container->setParameter('api_response.default_serializer', $mergedConfig['default_serializer']);
        $container->setParameter('api_response.default_config', $mergedConfig['defaults']);
        $container->setParameter('api_response.path_configs', $mergedConfig['paths']);
    }
}
