<?php

declare(strict_types=1);

namespace Atoolo\Resource;

use Atoolo\Resource\Exception\InvalidResourceException;
use Atoolo\Resource\Exception\ResourceNotFoundException;

class ResourceHierarchyFinder
{
    public function __construct(
        private readonly ResourceHierarchyLoader $loader,
    ) {}

    /**
     * Walks the tree of resources starting from the given resource and calls
     * the given function for each resource. Returns the resource where the
     * callable returns true.
     *
     * The callable function expects the following parameter:
     * - Resource: the current resource
     *
     * The callable function should return true if the current resource is the
     * one we are looking for.
     *
     *
     * @param Resource|ResourceLocation $base The resource to be used initially.
     *   If `$base` is a ResourceLocation the resource is loaded.
     * @param callable(Resource): bool $fn
     * @throws InvalidResourceException
     * @throws ResourceNotFoundException
     */
    public function findFirst(
        Resource|ResourceLocation $base,
        callable $fn,
    ): ?Resource {

        $walker = new ResourceHierarchyWalker($this->loader);

        $current = $walker->init($base);

        if ($fn($current) === true) {
            return $current;
        }
        while ($current = $walker->next()) {
            if ($fn($current) === true) {
                return $current;
            }
        }

        return null;
    }
}
