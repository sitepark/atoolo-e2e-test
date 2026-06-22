<?php

declare(strict_types=1);

namespace Atoolo\Resource\Loader;

use Atoolo\Resource\Exception\InvalidResourceException;
use Atoolo\Resource\Exception\ResourceNotFoundException;
use Atoolo\Resource\Resource;
use Atoolo\Resource\ResourceHierarchyLoader;
use Atoolo\Resource\ResourceLoader;
use Atoolo\Resource\ResourceLocation;

class SiteKitResourceHierarchyLoader implements ResourceHierarchyLoader
{
    public function __construct(
        private readonly ResourceLoader $resourceLoader,
        private readonly string $treeName,
    ) {}

    /**
     * @throws InvalidResourceException
     * @throws ResourceNotFoundException
     */
    public function load(ResourceLocation $location): Resource
    {
        return $this->resourceLoader->load($location);
    }

    public function exists(ResourceLocation $location): bool
    {
        return $this->resourceLoader->exists($location);
    }

    public function cleanup(): void
    {
        $this->resourceLoader->cleanup();
    }

    /**
     * @throws InvalidResourceException
     * @throws ResourceNotFoundException
     */
    public function loadRoot(ResourceLocation $location): Resource
    {
        $resource = $this->resourceLoader->load($location);
        $loaded = [$resource->location];
        while (!$this->isRoot($resource)) {
            $parent = $this->loadPrimaryParentResource($resource);
            if (in_array($parent->location, $loaded, true)) {
                $loaded[] = $parent->location;
                throw new InvalidResourceException(
                    $resource->toLocation(),
                    'circular parent reference detected: ' . implode(' -> ', $loaded),
                );
            }
            $resource = $parent;
            $loaded[] = $resource->location;
        }

        return $resource;
    }

    /**
     * @throws InvalidResourceException
     * @throws ResourceNotFoundException
     */
    public function loadPrimaryParent(ResourceLocation $location): ?Resource
    {
        $resource = $this->resourceLoader->load($location);
        if ($this->isRoot($resource)) {
            return null;
        }
        return $this->loadPrimaryParentResource($resource);
    }

    /**
     * @throws InvalidResourceException
     * @throws ResourceNotFoundException
     */
    public function loadParent(
        ResourceLocation $location,
        string $parentId,
    ): ?Resource {
        $resource = $this->resourceLoader->load($location);
        if ($this->isRoot($resource)) {
            return null;
        }
        return $this->loadParentResource($resource, $parentId);
    }

    /**
     * @return Resource[]
     * @throws InvalidResourceException if an encountered Resource has no
     * parent but is not considered a root.
     * @throws ResourceNotFoundException
     */
    public function loadPrimaryPath(ResourceLocation $location): array
    {
        $resource = $this->resourceLoader->load($location);
        $path = [$resource];
        $loaded = [$resource->location];
        while (!$this->isRoot($resource)) {
            $parent = $this->loadPrimaryParentResource($resource);
            if (in_array($parent->location, $loaded, true)) {
                $loaded[] = $parent->location;
                throw new InvalidResourceException(
                    $resource->toLocation(),
                    'circular parent reference detected: ' . implode(' -> ', $loaded),
                );
            }
            array_unshift($path, $parent);
            $resource = $parent;
            $loaded[] = $resource->location;
        }
        return $path;
    }

    /**
     * @return Resource[]
     *
     * @throws InvalidResourceException
     * @throws ResourceNotFoundException
     */
    public function loadChildren(ResourceLocation $location): array
    {
        $resource = $this->resourceLoader->load($location);

        $children = [];
        $childrenLocationList = $this->getChildrenLocations($resource);
        foreach ($childrenLocationList as $childLocation) {
            $children[] = $this->resourceLoader->load($childLocation);
        }

        return $children;
    }

    /**
     * @throws InvalidResourceException if no primary parent can be found
     * @throws ResourceNotFoundException
     */
    protected function loadPrimaryParentResource(
        Resource $resource,
    ): Resource {
        $parentLocation = $this->getPrimaryParentLocation($resource);
        if ($parentLocation === null) {
            throw new InvalidResourceException(
                $resource->toLocation(),
                'the resources should have a parent',
            );
        }
        return $this->resourceLoader->load($parentLocation);
    }

    protected function loadParentResource(
        Resource $resource,
        string $parentId,
    ): ?Resource {
        $parentLocation = $this->getParentLocation($resource, $parentId);
        if ($parentLocation === null) {
            return null;
        }
        return $this->resourceLoader->load($parentLocation);
    }

    public function isRoot(Resource $resource): bool
    {
        $parentList = $resource->data->getAssociativeArray(
            'base.trees.' . $this->treeName . '.parents',
        );

        return count($parentList) === 0;
    }

    /**
     * @param Resource $resource
     * @return ResourceLocation|null
     * @throws InvalidResourceException
     */
    public function getPrimaryParentLocation(
        Resource $resource,
    ): ?ResourceLocation {
        $parentList = $resource->data->getAssociativeArray(
            'base.trees.' . $this->treeName . '.parents',
        );

        if (
            count($parentList) === 0
        ) {
            return null;
        }

        $firstParent = null;
        foreach ($parentList as $parent) {
            if (!is_array($parent)) {
                throw new InvalidResourceException(
                    $resource->toLocation(),
                    'primary parent in ' .
                    'base.trees.' . $this->treeName . '.parents ' .
                    'is invalid',
                );
            }
            $firstParent ??= $parent;
            $isPrimary = $parent['isPrimary'] ?? false;
            if ($isPrimary === true) {
                if (!isset($parent['url'])) {
                    throw new InvalidResourceException(
                        $resource->toLocation(),
                        'primary parent in ' .
                            'base.trees.' . $this->treeName . '.parents ' .
                            'as no url',
                    );
                }
                if (!is_string($parent['url'])) {
                    throw new InvalidResourceException(
                        $resource->toLocation(),
                        'url of primary parent in ' .
                             'base.trees.' . $this->treeName . '.parents ' .
                             'is not a string',
                    );
                }
                return ResourceLocation::of(
                    $parent['url'],
                    $resource->lang,
                );
            }
        }

        if (!isset($firstParent['url'])) {
            throw new InvalidResourceException(
                $resource->toLocation(),
                'first parent in ' .
                    'base.trees.' . $this->treeName . '.parents ' .
                    'has no url',
            );
        }

        if (!is_string($firstParent['url'])) {
            throw new InvalidResourceException(
                $resource->toLocation(),
                'url of first parent in ' .
                    'base.trees.' . $this->treeName . '.parents ' .
                    'is not a string',
            );
        }

        return ResourceLocation::of(
            $firstParent['url'],
            $resource->lang,
        );
    }

    public function getParentLocation(
        Resource $resource,
        string $parentId,
    ): ?ResourceLocation {
        $parentList = $resource->data->getAssociativeArray(
            'base.trees.' . $this->treeName . '.parents',
        );

        if (
            count($parentList) === 0
        ) {
            return null;
        }

        foreach ($parentList as $id => $parent) {
            if (!is_array($parent)) {
                throw new InvalidResourceException(
                    $resource->toLocation(),
                    'parent in ' .
                    'base.trees.' . $this->treeName . '.parents ' .
                    'not an array',
                );
            }
            if ($parentId === (string) $id) {
                return ResourceLocation::of(
                    $parent['url'],
                    $resource->lang,
                );
            }
        }

        return null;
    }

    /**
     * @return ResourceLocation[]
     */
    public function getChildrenLocations(Resource $resource): array
    {
        $childrenList = $resource->data->getAssociativeArray(
            'base.trees.' . $this->treeName . '.children',
        );

        if (
            count($childrenList) === 0
        ) {
            return [];
        }

        return array_map(function ($child) use ($resource) {
            if (!is_array($child)) {
                throw new InvalidResourceException(
                    $resource->toLocation(),
                    'children in ' .
                    'base.trees.' . $this->treeName . '.children ' .
                    'not an array',
                );
            }
            return ResourceLocation::of(
                $child['url'],
                $resource->lang,
            );
        }, $childrenList);
    }
}
