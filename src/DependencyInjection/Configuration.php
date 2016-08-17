<?php

namespace MattJanssen\ApiResponseBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
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
    const SERIALIZER_JSON_GROUP_ENCODE = 'json_group_encode';
    const SERIALIZER_JMS_SERIALIZER = 'jms_serializer';

    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('api_response');

        $configNode = (new NodeBuilder())
            ->enumNode('serializer')
                ->values([
                    self::SERIALIZER_JSON_ENCODE,
                    self::SERIALIZER_JSON_GROUP_ENCODE,
                    self::SERIALIZER_JMS_SERIALIZER,
                ])
                ->defaultValue(self::SERIALIZER_JSON_ENCODE)
            ->end()
            ->scalarNode('cors_allow_origin_regex')->end()
            ->arrayNode('cors_allow_headers')
                ->prototype('scalar')->end()
            ->end()
            ->integerNode('cors_max_age')
                ->defaultValue(86400) // One day in seconds.
            ->end()
        ;

        $rootNode
            ->children()
                ->arrayNode('defaults')
                    ->append(clone $configNode)
                ->end()
                ->arrayNode('paths')
                    ->normalizeKeys(false)
                    ->prototype('array')
                        ->children()
                            ->append(clone $configNode)
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
