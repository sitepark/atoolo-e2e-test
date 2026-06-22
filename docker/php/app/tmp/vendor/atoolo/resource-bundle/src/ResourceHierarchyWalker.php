<?php

declare(strict_types=1);

namespace Atoolo\Resource;

use LogicException;

/**
 * The `ResourceHierarchyWalker` class is used to traverse a hierarchy of
 * resources.
 *
 * The walker needs a base resource to start with. This can be set with
 * `init()`.
 *
 * The walker can then be moved up and down in the hierarchy
 * with the help of methods like
 * - `down()`
 * - `child()`
 * - `up()`
 * - `nextSibling()`
 * - `previousSibling()`
 * - `next()`
 *
 * With these methods, the walker can only move below the base resource.
 * To move above the base resource, the methods `primaryParent()`
 * and `parent()` can be used.
 *
 * The walker can also be used to traverse the entire hierarchy
 * with the help of the `walk()` method.
 */
class ResourceHierarchyWalker
{
    private ?Resource $current = null;

    private ResourceLanguage $lang;

    /**
     * Contains the children and all parent children
     *  of the levels that have been traversed up to this point.
     *
     * @var array<array<ResourceLocation>>
     */
    private array $childrenStack = [];

    /**
     * Points to the children for all children lists of
     * $childrenStack`. $childrenStackPointer[0] indicates the
     * current position of `$childrenStack[0]`.
     *
     * @var array<int>
     */
    private array $childrenStackPointer = [];

    /**
     * @var Resource[]
     */
    private array $parentPath = [];

    public function __construct(
        private readonly ResourceHierarchyLoader $hierarchyLoader,
    ) {}

    /**
     * Defines the resource that the walker should initially use.
     * It is important to first set an initial resource so that
     * the walker can work.
     *
     * If the walker has already worked, it is reset.
     *
     * @param Resource|ResourceLocation $base The resource to be used initially.
     * *   If `$base` is a ResourceLocation the resource is loaded.
     */
    public function init(Resource|ResourceLocation $base): Resource
    {
        if ($base instanceof ResourceLocation) {
            $base = $this->hierarchyLoader->load($base);
        }
        $this->current = $base;
        $this->lang = $base->lang;
        $this->childrenStack = [];
        $this->childrenStackPointer = [];
        $this->parentPath = [];

        return $this->current;
    }

    /**
     * There can be more than one higher-level resource in the hierarchy of
     * a resource. One of these higher-level resources is always defined
     * as primary. This method can be used to set the walker to the primary
     * parent resource.
     *
     * Internally, `init()` is also called here with the determined parent.
     * So that the walker is also reset if necessary.
     *
     * @return Resource|null Returns null if the current resource is
     *  a root resource.
     * @throws LogicException If no current resource is set.
     */
    public function primaryParent(): ?Resource
    {
        if ($this->current === null) {
            throw new LogicException('No current resource set');
        }

        if ($this->hierarchyLoader->isRoot($this->current)) {
            return null;
        }

        $primaryParentLocation = $this->hierarchyLoader
            ->getPrimaryParentLocation($this->current);
        if ($primaryParentLocation === null) {
            return null;
        }

        $this->init($this->load(
            $primaryParentLocation->location,
        ));

        return $this->getCurrent();
    }

    /**
     * There can be more than one parent resource in the hierarchy of a
     * resource.
     * This method can be used to set the walker to a specific parent
     * resource.
     *
     * Internally, `init()` is also called here with the determined parent.
     * So that the walker is also reset if necessary.
     *
     * @return Resource|null Returns null if the current resource is
     * a root resource or the parent could not be determined.
     * @throws LogicException If no current resource is set.
     */
    public function parent(string $parentId): ?Resource
    {
        if ($this->current === null) {
            throw new LogicException('No current resource set');
        }

        if ($this->hierarchyLoader->isRoot($this->current)) {
            return null;
        }

        $secondaryParentLocation = $this->hierarchyLoader
            ->getParentLocation($this->current, $parentId);
        if ($secondaryParentLocation === null) {
            return null;
        }

        $this->init($this->load(
            $secondaryParentLocation->location,
        ));

        return $this->getCurrent();
    }

    /**
     * The resource where the Walker is currently positioned.
     * @throws LogicException If no current resource is set.
     */
    public function getCurrent(): Resource
    {
        if ($this->current === null) {
            throw new LogicException('No current resource set');
        }
        return $this->current;
    }

    /**
     * The level in which the walker is currently located.
     * Based on the resource that was initially used.
     */
    public function getLevel(): int
    {
        return count($this->parentPath);
    }

    /**
     * The path the walker has traveled up to the current element.
     * Based on the resource that was initially used.
     * The initial resource is the first, the current resource the
     * last element of the path.
     *
     * @return Resource[]
     */
    public function getPath(): array
    {
        if ($this->current === null) {
            return [];
        }

        $path = $this->parentPath;
        $path[] = $this->current;
        return $path;
    }

    /**
     * Walk to the next sibling. Please note that this does not work for
     * the initial resource that was set with `init()`, as the parent
     * resource is not known. This case can be solved with the help of
     * `primaryParent()` and `secondaryParent()`.
     *
     * ```
     * $walker->init($base);
     * $walker->primaryParent();
     * $walker->child($base->getId());
     * $walker->nextSibling();
     * ```
     *
     * @return Resource|null Returns null if there are no more siblings.
     */
    public function nextSibling(): ?Resource
    {
        if (empty($this->childrenStack)) {
            return null;
        }

        $topStackIndex = count($this->childrenStack) - 1;
        $children = $this->childrenStack[$topStackIndex];
        $pointer = $this->childrenStackPointer[$topStackIndex] + 1;

        if ($pointer >= count($children)) {
            return null;
        }

        $childLocation = $children[$pointer];
        $child = $this->load($childLocation->location);

        $this->childrenStackPointer[$topStackIndex] = $pointer;
        $this->current = $child;
        return $child;
    }

    /**
     * Walk to the previous sibling. Please note that this does not work for
     * the initial resource that was set with `init()`, as the parent
     * resource is not known. This case can be solved with the help of
     * `primaryParent()` and `secondaryParent()`.
     *
     * ```
     * $walker->init($base);
     * $walker->primaryParent();
     * $walker->child($base->getId());
     * $walker->previousSibling();
     * ```
     *
     * @return Resource|null Returns null if there are no more siblings.
     */
    public function previousSibling(): ?Resource
    {
        if (empty($this->childrenStack)) {
            return null;
        }

        $topStackIndex = count($this->childrenStack) - 1;
        $children = $this->childrenStack[$topStackIndex];
        $pointer = $this->childrenStackPointer[$topStackIndex] - 1;

        if ($pointer < 0) {
            return null;
        }

        //
        $childLocation = $children[$pointer];
        $child = $this->load($childLocation->location);

        $this->childrenStackPointer[$topStackIndex] = $pointer;
        $this->current = $child;
        return $child;
    }


    /**
     * Go up one level. This is only possible if the walker has already
     * gone down one level. If the parent element is to be determined
     * from an initial resource, `primaryParent` or `secondaryParent`
     * must be used.
     *
     * @return Resource|null Returns null if the walker is already at the
     * highest level.
     */
    public function up(): ?Resource
    {
        if ($this->current === null) {
            throw new LogicException('No current resource set');
        }

        if (empty($this->parentPath)) {
            return null;
        }

        $parent = array_pop($this->parentPath);

        array_pop($this->childrenStack);
        array_pop($this->childrenStackPointer);

        $this->current = $parent;

        return $this->current;
    }

    /**
     * Goes down one level. And use the first child.
     * This is only possible if the current resource also
     * has children.
     *
     * @return Resource|null Returns null if the current resource
     *  has no children.
     * @throws LogicException If no current resource is set.
     */
    public function down(): ?Resource
    {
        if ($this->current === null) {
            throw new LogicException('No current resource set');
        }

        $childrenLocations = $this->hierarchyLoader->getChildrenLocations(
            $this->current,
        );
        $children = array_values($childrenLocations);

        if (empty($children)) {
            return null;
        }

        $this->parentPath[] = $this->current;
        $this->current = $this->load($children[0]->location);

        $this->childrenStack[] = array_values($children);
        $this->childrenStackPointer[count($this->childrenStack) - 1] = 0;

        return $this->current;
    }

    /**
     * Goes down one level like `down()`. However, the first child resource
     * is not used here, instead `$childId` is used to specify which one is to
     * be used child resource.
     *
     * @param string $childId The ID of the child resource to be used.
     * @return Resource|null Returns null if the current resource has no
     *  children or the child resource could not be found.
     */
    public function child(string $childId): ?Resource
    {
        if ($this->current === null) {
            throw new LogicException('No current resource set');
        }

        $childrenLocations = $this->hierarchyLoader->getChildrenLocations(
            $this->current,
        );

        if (empty($childrenLocations)) {
            return null;
        }

        $childPointer = array_search(
            (int) $childId,
            array_keys($childrenLocations),
            true,
        );

        if ($childPointer === false) {
            return null;
        }

        $children = array_values($childrenLocations);

        $this->parentPath[] = $this->current;
        $this->current = $this->load($children[$childPointer]->location);

        $this->childrenStack[] = array_values($children);
        $this->childrenStackPointer[count($this->childrenStack) - 1] =
            $childPointer;

        return $this->current;
    }


    /**
     * This method runs through the hierarchy downwards from the
     * initial resource.
     *
     * @return Resource|null Returns `null` after the last resource in
     * the hierarchy has been run through.
     */
    public function next(): ?Resource
    {
        if ($this->current === null) {
            throw new LogicException('No current resource set');
        }

        $firstChildOfCurrent = $this->down();
        if ($firstChildOfCurrent !== null) {
            return $firstChildOfCurrent;
        }

        while (count($this->childrenStack) > 0) {
            $child = $this->nextSibling();
            if ($child !== null) {
                return $child;
            }
            $this->up();
        }

        return null;
    }


    /**
     * Traverses the entire hierarchy downwards and calls the `$fn` method
     * for each resource. Including the passed `$base` resource.
     * In the method, `init($base)` is called, which resets the walker
     * if necessary. Here `next()` is used to traverse the hierarchy.
     *
     * @param Resource|ResourceLocation $base The resource to be used initially.
     * *   If `$base` is a ResourceLocation the resource is loaded.
     * @param callable(Resource): void $fn The method to be called for each
     *  resource.
     */
    public function walk(Resource|ResourceLocation $base, callable $fn): void
    {
        $current = $this->init($base);
        $fn($current);
        while ($current = $this->next()) {
            $fn($current);
        }
    }

    private function load(string $path): Resource
    {
        $location = ResourceLocation::of($path, $this->lang);
        return $this->hierarchyLoader->load($location);
    }
}
