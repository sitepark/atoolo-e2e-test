<?php

declare(strict_types=1);

namespace Atoolo\Resource;

use Atoolo\Resource\Exception\InvalidResourceException;
use Atoolo\Resource\Exception\ResourceNotFoundException;

/**
 * The ResourceHierarchyLoader interface defines the methods used to load
 * resources or nodes whose hierarchical structure is defined in the resources.
 * For example, the navigation tree.
 */
interface ResourceHierarchyLoader extends ResourceLoader
{
    /**
     * Determines the root resource via the parent links contained in the
     * resource data.
     * @throws InvalidResourceException
     * @throws ResourceNotFoundException
     */
    public function loadRoot(
        ResourceLocation $location,
    ): Resource;

    /**
     * Determines the primary parent resource via the parent links
     * contained in the resource data.
     * @throws InvalidResourceException
     * @throws ResourceNotFoundException
     */
    public function loadPrimaryParent(
        ResourceLocation $location,
    ): ?Resource;

    /**
     * Determines the path to the root resource via the primary parent links
     * contained in the resource data.
     * The array contains the resources starting with the root resource. The
     * last element of the array is the resource of the passed `$location`
     * @return Resource[]
     * @throws InvalidResourceException
     * @throws ResourceNotFoundException
     */
    public function loadPrimaryPath(
        ResourceLocation $location,
    ): array;

    /**
     * Determines the children resources via the children links contained in the
     * resource data.
     * @return Resource[]
     * @throws InvalidResourceException
     * @throws ResourceNotFoundException
     */
    public function loadChildren(
        ResourceLocation $location,
    ): array;

    /**
     * Indicates whether the passed resource is a root resource.
     *
     * @param Resource $resource
     * @return bool
     */
    public function isRoot(Resource $resource): bool;

    /**
     * Determines the children locations via the children links contained in the
     * resource data.
     *
     * @return ResourceLocation[]
     */
    public function getChildrenLocations(Resource $resource): array;

    /**
     * Determines the primary parent location via the parent links contained in
     * the resource data.
     */
    public function getPrimaryParentLocation(
        Resource $resource,
    ): ?ResourceLocation;

    /**
     * Determines the secondary parent location via the parent links contained
     * in the resource data. A secondary parent is identified by the passed
     * `$parentId`.
     */
    public function getParentLocation(
        Resource $resource,
        string $parentId,
    ): ?ResourceLocation;
}
