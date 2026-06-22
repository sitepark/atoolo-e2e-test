<?php

declare(strict_types=1);

namespace Atoolo\Seo;

use Exception;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\GlobFileLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

/**
 * @codeCoverageIgnore
 */
class AtooloSeoBundle extends AbstractBundle
{
    /**
     * @throws Exception
     */
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
    }
    public function getPath(): string
    {
        return __DIR__;
    }
}
