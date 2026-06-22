<?php

declare(strict_types=1);

namespace Atoolo\Resource\Factory;

use Atoolo\Resource\DataBag;
use Atoolo\Resource\ResourceChannel;
use Atoolo\Resource\ResourceTenant;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * @phpstan-type ContextPhp array{
 *     server: array{
    *     host: string
 *     },
 *     tenant: array{
 *         id: string,
 *         name: string,
 *         anchor: string,
 *         attributes: array<string, mixed>
 *     },
 *     publisher: array{
 *          id: int,
 *          name: string,
 *          anchor: string,
 *          serverName: string,
 *          preview: bool,
 *          nature: ?string,
 *          locale: ?string,
 *          encoding: ?string,
 *          translationLocales: ?string[],
 *          attributes: ?array<string, mixed>,
 *     }
 * }
 */
#[AsAlias(id: 'atoolo_resource.resource_channel_factory')]
class SiteKitResourceChannelFactory implements ResourceChannelFactory
{
    private string $contextPhpFile;

    private string $resourceDir;

    private string $configDir;

    public function __construct(
        #[Autowire(param: 'atoolo_resource.resource_root')]
        private readonly string $baseDir,
    ) {
        if (empty($this->baseDir)) {
            throw new RuntimeException(
                'RESOURCE_ROOT not set',
            );
        }
    }

    public function create(): ResourceChannel
    {

        $this->determinePaths();

        $data = $this->loadContextPhpFile();

        $searchIndex = $data['publisher']['anchor'];

        return new ResourceChannel(
            (string) $data['publisher']['id'],
            $data['publisher']['name'],
            $data['publisher']['anchor'],
            $data['publisher']['serverName'],
            $data['publisher']['preview'],
            $data['publisher']['nature'] ?? 'internet',
            $data['publisher']['locale'] ?? 'de_DE',
            $this->baseDir,
            $this->resourceDir,
            $this->configDir,
            $searchIndex,
            $data['publisher']['translationLocales'] ?? [],
            new DataBag(array_merge(
                $data['tenant']['attributes'],
                $data['publisher']['attributes'] ?? [],
            )),
            $this->createTenant(array_merge($data['tenant'], ['host' => $data['server']['host']])),
        );
    }

    /**
     * @param array{
     *     id: string,
     *     name: string,
     *     anchor: string,
     *     host: string,
     *     attributes: array<string, mixed>,
     * } $data
     * @return ResourceTenant
     */
    private function createTenant(array $data): ResourceTenant
    {
        return new ResourceTenant(
            (string) $data['id'],
            $data['name'],
            $data['anchor'],
            $data['host'],
            new DataBag($data['attributes']),
        );
    }

    /**
     * @return ContextPhp
     */
    private function loadContextPhpFile(): array
    {
        $context = require $this->contextPhpFile;
        if (!is_array($context)) {
            throw new RuntimeException(
                'context.php must return an array',
            );
        }

        if (isset($context['client'])) {
            $context['tenant'] = $context['client'];
            unset($context['client']);
        }

        /** @var ContextPhp $context */
        return $context;
    }

    private function determinePaths(): void
    {
        $resourceLayoutContextPhpFile = $this->baseDir . '/context.php';

        if (file_exists($resourceLayoutContextPhpFile)) {
            $this->contextPhpFile = $resourceLayoutContextPhpFile;
            $this->resourceDir = $this->baseDir . '/objects';
            $this->configDir = $this->baseDir . '/configs';
            return;
        }

        $documentRootLayoutContextPhpFile =
            $this->baseDir . '/WEB-IES/context.php';

        if (!file_exists($documentRootLayoutContextPhpFile)) {
            throw new RuntimeException(
                'context.php does not exists: ' .
                $resourceLayoutContextPhpFile . ' or ' .
                $documentRootLayoutContextPhpFile,
            );
        }

        $this->contextPhpFile = $documentRootLayoutContextPhpFile;
        $this->resourceDir = $this->baseDir;
        $this->configDir = $this->baseDir;
    }
}
