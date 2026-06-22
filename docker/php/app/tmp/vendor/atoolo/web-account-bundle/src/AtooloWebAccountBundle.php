<?php

declare(strict_types=1);

namespace Atoolo\WebAccount;

use Exception;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\GlobFileLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * @codeCoverageIgnore
 */
class AtooloWebAccountBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        /** @phpstan-ignore-next-line */
        $definition->rootNode()
            ->children()
            ->integerNode('token_ttl')
            ->defaultValue(60 * 60 * 24 * 30)
            ->min(60 * 60)
            ->end()
            ->integerNode('registration_token_ttl')
            ->defaultValue(60 * 60 * 2)
            ->min(60 * 10)
            ->end()
            ->integerNode('password_reset_token_ttl')
            ->defaultValue(60 * 60 * 2)
            ->min(60 * 10)
            ->end()
            ->stringNode('unauthorized_entry_point')
            ->defaultValue('/account')
            ->end()
            ->end();
    }

    /**
     * @param array<string, mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->parameters()->set('atoolo_web_account.token_ttl', $config['token_ttl']);
        $container->parameters()->set('atoolo_web_account.registration_token_ttl', $config['registration_token_ttl']);
        $container->parameters()->set(
            'atoolo_web_account.password_reset_token_ttl',
            $config['password_reset_token_ttl'],
        );
        $container->parameters()->set(
            'atoolo_web_account.unauthorized_entry_point',
            $config['unauthorized_entry_point'],
        );
    }

    /**
     * @throws Exception
     */
    public function build(ContainerBuilder $container): void
    {

        $container->setParameter(
            'atoolo_web_account.src_dir',
            __DIR__,
        );

        $configDir = __DIR__ . '/../config';

        $container->setParameter(
            'atoolo_web_account.config_dir',
            $configDir,
        );

        $loader = new GlobFileLoader(new FileLocator($configDir));
        $loader->setResolver(
            new LoaderResolver(
                [
                    new YamlFileLoader($container, new FileLocator($configDir)),
                ],
            ),
        );

        $loader->load('graphql.yaml');
        $loader->load('services.yaml');
    }
}
