<?php

declare(strict_types=1);

namespace Atoolo\Resource\Test;

use Atoolo\Resource\Loader\SiteKitResourceHierarchyLoader;
use Atoolo\Resource\ResourceHierarchyFinder;
use Atoolo\Resource\ResourceLoader;
use Atoolo\Resource\ResourceLocation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ResourceHierarchyFinder::class)]
class ResourceHierarchyFinderTest extends TestCase
{
    private ResourceLoader $loader;

    private ResourceHierarchyFinder $finder;

    public function setUp(): void
    {
        $resourceBaseDir = realpath(
            __DIR__ . '/resources/' .
            'ResourceHierarchyFinder',
        );
        $this->loader = $this->createStub(
            ResourceLoader::class,
        );
        $this->loader->method('load')
            ->willReturnCallback(static function ($location) use (
                $resourceBaseDir
            ) {
                return include $resourceBaseDir . $location->location;
            });

        $hierarchyLoader = new SiteKitResourceHierarchyLoader(
            $this->loader,
            'category',
        );
        $this->finder = new ResourceHierarchyFinder(
            $hierarchyLoader,
        );
    }

    public function testFindFirst(): void
    {
        $resource = $this->finder->findFirst(
            ResourceLocation::of('/a.php'),
            static function ($resource) {
                return $resource->id === 'c';
            },
        );

        $this->assertEquals(
            'c',
            $resource->id,
            'Resource c should be found',
        );
    }

    public function testFindFirstFindBase(): void
    {
        $resource = $this->finder->findFirst(
            ResourceLocation::of('/a.php'),
            static function ($resource) {
                return $resource->id === 'a';
            },
        );

        $this->assertEquals(
            'a',
            $resource->id,
            'Resource a should be found',
        );
    }

    public function testFindFirstNotFound(): void
    {
        $resource = $this->finder->findFirst(
            ResourceLocation::of('/a.php'),
            static function ($resource) {
                return $resource->id === 'x';
            },
        );

        $this->assertNull(
            $resource,
            'Resource should not be found',
        );
    }
}
