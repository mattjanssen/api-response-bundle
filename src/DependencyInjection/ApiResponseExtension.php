<?php

namespace MattJanssen\ApiResponseBundle\DependencyInjection;

use MattJanssen\ApiResponseBundle\Compiler\ApiConfigCompiler;
use MattJanssen\ApiResponseBundle\Model\ApiConfig;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
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
        // Create the API Config compiler service.
        // @TODO Use PHP 7 null coalescing operator.
        $defaults = isset($mergedConfig['defaults']) ? $mergedConfig['defaults'] : [];

        $paths = $mergedConfig['paths'];

//        $configCompiler = new ApiConfigCompiler($defaultConfig, $pathConfigs);
//        $container->set('api_response.compiler.api_config', $configCompiler);

//        $configCompilerDefinition = new Definition(ApiConfigCompiler::class, [
//            $defaultConfig,
//            $pathConfigs,
//        ]);
//        $container->register('api_response.compiler.api_config', $configCompilerDefinition);

        $container->setParameter('api_response.defaults', $defaults);
        $container->setParameter('api_response.paths', $paths);

        // Load the rest of the services.
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
