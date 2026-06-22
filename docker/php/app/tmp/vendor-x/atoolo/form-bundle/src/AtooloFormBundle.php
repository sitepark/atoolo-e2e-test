<?php

declare(strict_types=1);

namespace Atoolo\Form;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\GlobFileLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * @codeCoverageIgnore
 */
class AtooloFormBundle extends AbstractBundle
{
    public function build(ContainerBuilder $container): void
    {
        $configDir = __DIR__ . '/../config';

        $loader = new GlobFileLoader(new FileLocator($configDir));
        $loader->setResolver(
            new LoaderResolver(
                [
                    new YamlFileLoader($container, new FileLocator($configDir)),
                ],
            ),
        );

        $loader->load('services.yaml');
        $loader->load('rate_limiter.yaml');
    }

    /* TODO actually, it shouldn't work at all without it
     * https://symfony.com/doc/current/bundles.html
    public function getPath(): string
    {
        return __DIR__;
    }
    */
}
