<?php

namespace MattJanssen\ApiWrapBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * API Wrap Bundle Configuration
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
        $rootNode = $treeBuilder->root('api_wrap');

        $rootNode
            ->children()
                ->enumNode('serializer')
                    ->values([
                        self::SERIALIZER_JSON_ENCODE,
                        self::SERIALIZER_JMS_SERIALIZER,
                        self::SERIALIZER_FRACTAL,
                    ])
                    ->defaultValue(self::SERIALIZER_JSON_ENCODE)
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
