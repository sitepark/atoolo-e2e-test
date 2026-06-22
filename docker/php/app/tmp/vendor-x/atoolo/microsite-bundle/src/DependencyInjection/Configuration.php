<?php

declare(strict_types=1);

namespace Atoolo\Microsite\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('atoolo_microsite');

        // @phpstan-ignore method.notFound
        $treeBuilder->getRootNode()
            ->children()
            ->arrayNode('mountable_object_types')
            ->scalarPrototype()->end()
            ->defaultValue([])
            ->end()
            ->end();

        return $treeBuilder;
    }
}
