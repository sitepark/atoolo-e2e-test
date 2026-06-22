<?php

declare(strict_types=1);

namespace Atoolo\Resource\Loader;

use Atoolo\Resource\Exception\InvalidResourceException;
use Atoolo\Resource\Exception\ResourceNotFoundException;
use Atoolo\Resource\Resource;
use Atoolo\Resource\ResourceLoader;
use Atoolo\Resource\ResourceLocation;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * The CachedResourceLoader class is used to load resources
 * from a given location and cache them for future use.
 * The cache is stored in memory and is not persistent.
 */
#[AsAlias(id: 'atoolo_resource.cached_resource_loader')]
class CachedResourceLoader implements ResourceLoader
{
    /**
     * @var array<string, Resource>
     */
    private array $cache = [];
    public function __construct(
        #[Autowire(service: 'atoolo_resource.resource_loader')]
        private readonly ResourceLoader $resourceLoader,
    ) {}

    /**
     * @throws InvalidResourceException
     * @throws ResourceNotFoundException
     */
    public function load(ResourceLocation $location): Resource
    {
        $key = $location->__toString();
        return $this->cache[$key] ??= $this->resourceLoader->load(
            $location,
        );
    }

    public function exists(ResourceLocation $location): bool
    {
        $key = $location->__toString();
        if (isset($this->cache[$key])) {
            return true;
        }
        return $this->resourceLoader->exists($location);
    }

    public function cleanup(): void
    {
        $this->resourceLoader->cleanup();
        $this->cache = [];
    }
}
