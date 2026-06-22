<?php

declare(strict_types=1);

namespace Atoolo\Resource\Test\Loader;

use Atoolo\Resource\Exception\InvalidResourceException;
use Atoolo\Resource\Loader\SiteKitResourceHierarchyLoader;
use Atoolo\Resource\ResourceLoader;
use Atoolo\Resource\ResourceLocation;
use Atoolo\Resource\Test\TestResourceFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

#[CoversClass(SiteKitResourceHierarchyLoader::class)]
class SiteKitResourceHierarchyLoaderTest extends TestCase
{
    private SiteKitResourceHierarchyLoader $hierarchyLoader;

    public function setUp(): void
    {
        $this->hierarchyLoader = $this->createLoader(
            SiteKitResourceHierarchyLoader::class,
        );
    }

    private function createLoader(string $className)
    {
        $resourceBaseDir = realpath(
            __DIR__ . '/../resources/' .
            'Loader/SiteKitResourceHierarchyLoader',
        );
        $resourceLoader = $this->createStub(
            ResourceLoader::class,
        );
        $resourceLoader->method('load')
            ->willReturnCallback(static function ($location) use (
                $resourceBaseDir
            ) {
                $resource =  include $resourceBaseDir . $location->location;
                $error = error_get_last();
                return $resource;
            });

        return new $className(
            $resourceLoader,
            'category',
        );
    }

    /**
     * @throws Exception
     */
    public function testLoad(): void
    {
        $resourceLoader = $this->createMock(ResourceLoader::class);
        $hierarchyLoader = new SiteKitResourceHierarchyLoader(
            $resourceLoader,
            'category',
        );

        $resourceLoader->expects($this->once())
            ->method('load');

        $hierarchyLoader->load(ResourceLocation::of('/a.php'));
    }

    /**
     * @throws Exception
     */
    public function testExists(): void
    {
        $resourceLoader = $this->createMock(ResourceLoader::class);
        $hierarchyLoader = new SiteKitResourceHierarchyLoader(
            $resourceLoader,
            'category',
        );

        $resourceLoader->expects($this->once())
            ->method('exists');

        $hierarchyLoader->exists(ResourceLocation::of('/a.php'));
    }

    public function testCleanUp(): void
    {
        $resourceLoader = $this->createMock(ResourceLoader::class);
        $hierarchyLoader = new SiteKitResourceHierarchyLoader(
            $resourceLoader,
            'category',
        );

        $resourceLoader->expects($this->once())
            ->method('cleanup');

        $hierarchyLoader->cleanup();
    }

    public function testLoadPrimaryParentResourceWithoutParent(): void
    {

        $loader = $this->createLoader(
            TestSiteKitResourceHierarchyLoader::class,
        );
        $this->expectException(InvalidResourceException::class);
        $loader->loadRoot(ResourceLocation::of('/a.php'));
    }

    public function testLoadRoot(): void
    {
        $root = $this->hierarchyLoader->loadRoot(
            ResourceLocation::of('/c.php'),
        );

        $this->assertEquals(
            'a',
            $root->id,
            'unexpected root',
        );
    }

    public function testLoadRootWithSelfRecursion(): void
    {
        $this->expectException(InvalidResourceException::class);
        $this->hierarchyLoader->loadRoot(
            ResourceLocation::of('/withSelfRecursion.php'),
        );
    }

    public function testLoadRootWithRecursion(): void
    {
        $this->expectException(InvalidResourceException::class);
        $this->hierarchyLoader->loadRoot(
            ResourceLocation::of('/withRecursionA.php'),
        );
    }

    public function testIsRoot(): void
    {
        $root = TestResourceFactory::create([]);
        $isRoot = $this->hierarchyLoader->isRoot($root);

        $this->assertTrue(
            $isRoot,
            'should be root',
        );
    }

    public function testLoadPrimaryParent(): void
    {
        $parent = $this->hierarchyLoader->loadPrimaryParent(
            ResourceLocation::of('/c.php'),
        );

        $this->assertEquals(
            'b',
            $parent->id,
            'unexpected parent',
        );
    }

    public function testLoadChildren(): void
    {
        $children = $this->hierarchyLoader->loadChildren(
            ResourceLocation::of('/b.php'),
        );

        $childrenIdList = array_map(function ($child) {
            return $child->id;
        }, $children);

        $this->assertEquals(
            ['c'],
            $childrenIdList,
            'unexpected children',
        );
    }

    public function testLoadChildrenWithInvalidData(): void
    {
        $this->expectException(InvalidResourceException::class);
        $this->hierarchyLoader->loadChildren(
            ResourceLocation::of('/childrenWithInvalidData.php'),
        );
    }

    public function testLoadWithoutChildren(): void
    {
        $children = $this->hierarchyLoader->loadChildren(
            ResourceLocation::of('/c.php'),
        );

        $this->assertCount(
            0,
            $children,
            'children should be empty',
        );
    }

    public function testLoadPrimaryPath(): void
    {
        $path = $this->hierarchyLoader->loadPrimaryPath(
            ResourceLocation::of('/c.php'),
        );

        $pathIdList = array_map(function ($resource) {
            return $resource->id;
        }, $path);

        $this->assertEquals(
            ['a', 'b', 'c'],
            $pathIdList,
            'unexpected path',
        );
    }

    public function testLoadPrimaryPathWithSelfRecursion(): void
    {
        $this->expectException(InvalidResourceException::class);
        $this->hierarchyLoader->loadPrimaryPath(
            ResourceLocation::of('/withSelfRecursion.php'),
        );
    }

    public function testLoadPrimaryPathWithRecursion(): void
    {
        $this->expectException(InvalidResourceException::class);
        $this->hierarchyLoader->loadPrimaryPath(
            ResourceLocation::of('/withRecursionA.php'),
        );
    }

    public function testLoadPrimaryParentWithoutParent(): void
    {
        $parent = $this->hierarchyLoader->loadPrimaryParent(
            ResourceLocation::of('/a.php'),
        );
        $this->assertNull($parent, 'parent should be null');
    }

    public function testLoadRootResourcePrimaryParentWithoutUrl(): void
    {
        $this->expectException(InvalidResourceException::class);
        $this->hierarchyLoader->loadPrimaryParent(
            ResourceLocation::of('/primaryParentWithoutUrl.php'),
        );
    }

    public function testLoadRootResourcePrimaryParentWithInvalidData(): void
    {
        $this->expectException(InvalidResourceException::class);
        $this->hierarchyLoader->loadPrimaryParent(
            ResourceLocation::of('/primaryParentWithInvalidData.php'),
        );
    }

    public function testLoadRootResourcePrimaryParentWithNonStringUrl(): void
    {
        $this->expectException(InvalidResourceException::class);
        $this->hierarchyLoader->loadPrimaryParent(
            ResourceLocation::of('/primaryParentWithNonStringUrl.php'),
        );
    }

    public function testLoadRootResourceFirstParentWithoutUrl(): void
    {
        $this->expectException(InvalidResourceException::class);
        $this->hierarchyLoader->loadPrimaryParent(
            ResourceLocation::of('/firstParentWithoutUrl.php'),
        );
    }

    public function testLoadRootResourceFirstParentWithNonStringUrl(): void
    {
        $this->expectException(InvalidResourceException::class);
        $this->hierarchyLoader->loadPrimaryParent(
            ResourceLocation::of('/firstParentWithNonStringUrl.php'),
        );
    }

    public function testLoadParent(): void
    {
        $parent = $this->hierarchyLoader->loadParent(
            ResourceLocation::of('/b.php'),
            'a',
        );
        $this->assertEquals(
            'a',
            $parent->id,
            'unexpected parent',
        );
    }

    public function testLoadParentIdNotFound(): void
    {
        $parent = $this->hierarchyLoader->loadParent(
            ResourceLocation::of('/b.php'),
            'x',
        );
        $this->assertNull(
            $parent,
            'parent should be null',
        );
    }

    public function testLoadParentOfRoot(): void
    {
        $parent = $this->hierarchyLoader->loadParent(
            ResourceLocation::of('/a.php'),
            'x',
        );
        $this->assertNull(
            $parent,
            'parent should be null',
        );
    }

    public function testGetParentLocationWithoutParents(): void
    {
        $resource = TestResourceFactory::create([]);
        $parent = $this->hierarchyLoader->getParentLocation(
            $resource,
            'x',
        );
        $this->assertNull(
            $parent,
            'parent should be null',
        );
    }

    public function testGetParentLocationWithInvalidData(): void
    {
        $resource = TestResourceFactory::create([
            'base' => [
                'trees' => [
                    'category' => [
                        'parents' => [
                            'a' => 'invalid',
                        ],
                    ],
                ],
            ],
        ]);

        $this->expectException(InvalidResourceException::class);
        $this->hierarchyLoader->getParentLocation(
            $resource,
            'x',
        );
    }

    public function testGetParentLocationWithParentIdNotFound(): void
    {
        $resource = TestResourceFactory::create([
            'base' => [
                'trees' => [
                    'category' => [
                        'parents' => [
                            'a' => [
                                'id' => 'a',
                                'url' => '/a.php',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $parent = $this->hierarchyLoader->getParentLocation(
            $resource,
            'x',
        );
        $this->assertNull(
            $parent,
            'parent should be null',
        );
    }
}
