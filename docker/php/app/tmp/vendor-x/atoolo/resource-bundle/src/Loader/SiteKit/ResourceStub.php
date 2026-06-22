<?php

declare(strict_types=1);

namespace Atoolo\Resource\Loader\SiteKit;

/**
 * Provides the behavior of a resource needed to load a SiteKit resource.
 * @codeCoverageIgnore
 */
class ResourceStub
{
    /**
     * @var array<string, mixed>
     */
    private array $data = [];

    /**
     * @param array<string, mixed> $data
     */
    public function init(array $data): void
    {
        $this->data = array_merge($this->data, $data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function process(string $name, array $data): void
    {
        $this->data[$name] = $data;
    }

    /**
     * @return array<string, mixed>
     */
    public function getData(): array
    {
        return $this->data;
    }
}
