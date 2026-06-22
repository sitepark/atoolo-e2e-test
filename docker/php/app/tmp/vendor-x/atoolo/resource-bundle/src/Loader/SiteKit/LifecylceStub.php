<?php

declare(strict_types=1);

namespace Atoolo\Resource\Loader\SiteKit;

/**
 * Provides the behavior of a lifecylce needed to load a SiteKit resource.
 * @codeCoverageIgnore
 */
class LifecylceStub
{
    /**
     * @param array<string, mixed> $data
     */
    public function init(array $data): ResourceStub
    {
        $resource = new ResourceStub();
        $resource->init($data);
        return $resource;
    }

    public function finish(ResourceStub $resource): bool
    {
        return false;
    }

    public function process(string $name, ResourceStub $resource): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function service(ResourceStub $resource): array
    {
        return $resource->getData();
    }
}
