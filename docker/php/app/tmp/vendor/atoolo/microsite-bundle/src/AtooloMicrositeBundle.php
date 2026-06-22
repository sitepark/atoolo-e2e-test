<?php

declare(strict_types=1);

namespace Atoolo\Microsite;

use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * @codeCoverageIgnore
 */
class AtooloMicrositeBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        // @phpstan-ignore method.notFound
        $definition->rootNode()
            ->children()
            ->arrayNode('mountable_object_types')
            ->scalarPrototype()->end()
            ->defaultValue([])
            ->end()
            ->end();
    }

    /**
     * @param array{mountable_object_types: array<string>} $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->parameters()
            ->set('atoolo_microsite.mountable_object_types', $config['mountable_object_types']);
        $container->import(__DIR__ . '/../config/services.yaml');
    }
}
