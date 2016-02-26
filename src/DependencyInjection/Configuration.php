<?php

namespace MattJanssen\ApiResponseBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * API Response Bundle Configuration
 *
 * @author Matt Janssen <matt@mattjanssen.com>
 */
class Configuration implements ConfigurationInterface
{
    const SERIALIZER_JSON_ENCODE = 'json_encode';
    const SERIALIZER_JMS_SERIALIZER = 'jms_serializer';
    const SERIALIZER_FRACTAL = 'fractal';

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('api_response');

        $rootNode
            ->children()
                ->enumNode('default_serializer')
                    ->values([
                        self::SERIALIZER_JSON_ENCODE,
                        self::SERIALIZER_JMS_SERIALIZER,
                        self::SERIALIZER_FRACTAL,
                    ])
                    ->defaultValue(self::SERIALIZER_JSON_ENCODE)
                ->end()
                ->arrayNode('paths')
                    ->normalizeKeys(false)
                    ->prototype('array')
                        ->children()
                            ->scalarNode('cors_allow_origin_regex')->end()
                            ->arrayNode('cors_allow_headers')
                                ->prototype('scalar')->end()
                            ->end()
                            ->integerNode('cors_max_age')
                                ->defaultValue(86400) // One day in seconds.
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
