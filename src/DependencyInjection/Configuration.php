<?php

namespace MattJanssen\ApiResponseBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
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
    const SERIALIZER_ARRAY = 'array';
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

        $this->buildConfigNode(
            $rootNode->children()
                ->arrayNode('defaults')
                    ->children()
                        ->arrayNode('pattern')
                            ->prototype('scalar')->isRequired()->end()
                        ->end()
        );

        $this->buildConfigNode(
            $rootNode->children()
                ->arrayNode('paths')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                        ->scalarNode('pattern')->end()
        );

        return $treeBuilder;
    }

    /**
     * @param NodeBuilder $nodeBuilder
     *
     * @return ArrayNodeDefinition
     */
    private function buildConfigNode(NodeBuilder $nodeBuilder)
    {
        return $nodeBuilder
            ->enumNode('serializer')
                ->values([
                    self::SERIALIZER_ARRAY,
                    self::SERIALIZER_JSON_ENCODE,
                    self::SERIALIZER_JSON_GROUP_ENCODE,
                    self::SERIALIZER_JMS_SERIALIZER,
                ])
            ->end()
            ->arrayNode('serialize_groups')
                ->prototype('scalar')->end()
            ->end()
            ->scalarNode('cors_allow_origin_regex')->end()
            ->arrayNode('cors_allow_headers')
                ->prototype('scalar')->end()
            ->end()
            ->integerNode('cors_max_age')->end()
        ;
    }
}
