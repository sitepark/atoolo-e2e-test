<?php

declare(strict_types=1);

namespace Atoolo\Resource\Test;

use Atoolo\Resource\Loader\SiteKitResourceHierarchyLoader;
use Atoolo\Resource\ResourceHierarchyLoader;
use Atoolo\Resource\ResourceHierarchyWalker;
use Atoolo\Resource\ResourceLoader;
use Atoolo\Resource\ResourceLocation;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

#[CoversClass(ResourceHierarchyWalker::class)]
class ResourceHierarchyWalkerTest extends TestCase
{
    private ResourceLoader $loader;

    private ResourceHierarchyWalker $walker;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $resourceBaseDir = realpath(
            __DIR__ . '/resources/' .
            'ResourceHierarchyWalker',
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

        $this->walker = new ResourceHierarchyWalker(
            $hierarchyLoader,
        );
    }

    public function testInitWithLocation(): void
    {
        $this->walker->init(ResourceLocation::of('/1.php'));

        $this->assertEquals(
            '1',
            $this->walker->getCurrent()->id,
            'base resource should be found',
        );
    }

    public function testGetCurrentWithoutInit(): void
    {
        $this->expectException(LogicException::class);
        $this->walker->getCurrent();
    }

    public function testInitWithResource(): void
    {
        $base = $this->loader->load(ResourceLocation::of('/1.php'));
        $this->walker->init($base);

        $this->assertEquals(
            '1',
            $this->walker->getCurrent()->id,
            'base resource should be found',
        );
    }

    public function testPrimaryParent(): void
    {
        $base = $this->loader->load(ResourceLocation::of('/1/1.php'));
        $this->walker->init($base);

        $this->walker->primaryParent();

        $this->assertEquals(
            '1',
            $this->walker->getCurrent()->id,
            'unexpected primary parent',
        );
    }

    public function testPrimaryParentWithoutInit(): void
    {
        $this->expectException(LogicException::class);
        $this->walker->primaryParent();
    }

    public function testPrimaryParentOfRoot(): void
    {
        $base = $this->loader->load(ResourceLocation::of('/root.php'));
        $this->walker->init($base);

        $this->assertNull(
            $this->walker->primaryParent(),
            'root should not have a primary parent',
        );
    }

    /**
     * @throws Exception
     */
    public function testPrimaryParentWithoutPrimaryParent(): void
    {
        $hierarchyLoader = $this->createStub(
            ResourceHierarchyLoader::class,
        );
        $hierarchyLoader->method('getPrimaryParentLocation')
            ->willReturn(null);

        $walker = new ResourceHierarchyWalker(
            $hierarchyLoader,
        );
        $base = new \Atoolo\Resource\Resource(
            '',
            '',
            '',
            '',
            \Atoolo\Resource\ResourceLanguage::default(),
            new \Atoolo\Resource\DataBag([]),
        );

        $walker->init($base);

        $this->assertNull(
            $walker->primaryParent(),
            'root should not have a primary parent',
        );
    }

    public function testParent(): void
    {
        $base = $this->loader->load(ResourceLocation::of('/1/1.php'));
        $this->walker->init($base);

        $this->walker->parent('2');

        $this->assertEquals(
            '2',
            $this->walker->getCurrent()->id,
            'unexpected secondary parent',
        );
    }

    public function testParentWithoutInit(): void
    {
        $this->expectException(LogicException::class);
        $this->walker->parent('1');
    }

    public function testParentOfRoot(): void
    {
        $base = $this->loader->load(ResourceLocation::of('/root.php'));
        $this->walker->init($base);

        $this->assertNull(
            $this->walker->parent('1'),
            'root should not have a primary parent',
        );
    }

    /**
     * @throws Exception
     */
    public function testParentWithoutPrimaryParent(): void
    {
        $hierarchyLoader = $this->createStub(
            ResourceHierarchyLoader::class,
        );
        $hierarchyLoader->method('getPrimaryParentLocation')
            ->willReturn(null);

        $walker = new ResourceHierarchyWalker(
            $hierarchyLoader,
        );
        $base = new \Atoolo\Resource\Resource(
            '',
            '',
            '',
            '',
            \Atoolo\Resource\ResourceLanguage::default(),
            new \Atoolo\Resource\DataBag([]),
        );

        $walker->init($base);

        $this->assertNull(
            $walker->parent('1'),
            'root should not have a primary parent',
        );
    }

    public function testGetLevel(): void
    {
        $base = $this->loader->load(ResourceLocation::of('/1.php'));
        $this->walker->init($base);
        $this->walker->down();

        $this->assertEquals(
            1,
            $this->walker->getLevel(),
            'level should be 1',
        );
    }

    public function testGetPath(): void
    {
        $base = $this->loader->load(ResourceLocation::of('/1.php'));
        $this->walker->init($base);
        $this->walker->down();

        $expected = ['1', '1-1'];

        $this->assertEquals(
            $expected,
            array_map(
                static fn($resource) => $resource->id,
                $this->walker->getPath(),
            ),
            'unexpected path',
        );
    }

    public function testGetPathWithoutInit(): void
    {
        $this->assertEmpty(
            $this->walker->getPath(),
            'path should be empty',
        );
    }

    public function testNextSibling(): void
    {
        $base = $this->loader->load(ResourceLocation::of('/root.php'));
        $this->walker->init($base);
        $this->walker->down();
        $this->walker->nextSibling();

        $this->assertEquals(
            '2',
            $this->walker->getCurrent()->id,
            'unexpected sibling',
        );
    }

    public function testNextSiblingWithoutInit(): void
    {
        $this->assertNull(
            $this->walker->nextSibling(),
            'nextSibling should be null',
        );
    }

    public function testPreviousSibling(): void
    {
        $base = $this->loader->load(ResourceLocation::of('/root.php'));
        $this->walker->init($base);
        $this->walker->down();
        $this->walker->nextSibling();
        $this->walker->previousSibling();

        $this->assertEquals(
            '1',
            $this->walker->getCurrent()->id,
            'unexpected sibling',
        );
    }

    public function testPreviousSiblingWithoutInit(): void
    {
        $this->assertNull(
            $this->walker->previousSibling(),
            'previousSibling should be null',
        );
    }

    public function testPreviousSiblingWithFirstChild(): void
    {
        $base = $this->loader->load(ResourceLocation::of('/root.php'));
        $this->walker->init($base);
        $this->walker->down();
        $this->walker->previousSibling();

        $this->assertNull(
            $this->walker->previousSibling(),
            'previousSibling should be null',
        );
    }

    public function testUp(): void
    {
        $base = $this->loader->load(ResourceLocation::of('/root.php'));
        $this->walker->init($base);
        $this->walker->down();
        $this->walker->up();

        $this->assertEquals(
            'root',
            $this->walker->getCurrent()->id,
            'unexpected current resource',
        );
    }

    public function testUpWithoutInit(): void
    {
        $this->expectException(LogicException::class);
        $this->walker->up();
    }

    public function testUpForFirstLevel(): void
    {
        $base = $this->loader->load(ResourceLocation::of('/root.php'));
        $this->walker->init($base);

        $this->assertNull(
            $this->walker->up(),
            'up should be null',
        );
    }

    public function testDown(): void
    {
        $base = $this->loader->load(ResourceLocation::of('/root.php'));
        $this->walker->init($base);
        $this->walker->down();

        $this->assertEquals(
            '1',
            $this->walker->getCurrent()->id,
            'unexpected current resource',
        );
    }

    public function testDownWithoutInit(): void
    {
        $this->expectException(LogicException::class);
        $this->walker->down();
    }

    public function testChild(): void
    {
        $base = $this->loader->load(ResourceLocation::of('/root.php'));
        $this->walker->init($base);
        $this->walker->child('2');

        $this->assertEquals(
            '2',
            $this->walker->getCurrent()->id,
            'unexpected current resource',
        );
    }

    public function testChildNotFound(): void
    {
        $base = $this->loader->load(ResourceLocation::of('/root.php'));
        $this->walker->init($base);

        $this->assertNull(
            $this->walker->child('7'),
            'child should be null',
        );
    }

    public function testChildWithoutInit(): void
    {
        $this->expectException(LogicException::class);
        $this->walker->child('2');
    }

    /**
     * @throws Exception
     */
    public function testChildWithoutChildren(): void
    {
        $hierarchyLoader = $this->createStub(
            ResourceHierarchyLoader::class,
        );
        $hierarchyLoader->method('getChildrenLocations')
            ->willReturn([]);

        $walker = new ResourceHierarchyWalker(
            $hierarchyLoader,
        );
        $base = TestResourceFactory::create([]);

        $walker->init($base);

        $this->assertNull(
            $walker->child('1'),
            'child should be null',
        );
    }

    public function testNext(): void
    {
        $base = $this->loader->load(ResourceLocation::of('/root.php'));
        $this->walker->init($base);
        $this->walker->next();

        $this->assertEquals(
            '1',
            $this->walker->getCurrent()->id,
            'unexpected current resource',
        );
    }

    public function testNextWithoutInit(): void
    {
        $this->expectException(LogicException::class);
        $this->walker->next();
    }

    public function testWalkWithResource(): void
    {
        $root = $this->loader->load(ResourceLocation::of('/root.php'));
        $idList = [];
        $this->walker->walk($root, function ($resource) use (&$idList) {
            $idList[] = $resource->id;
        });

        $expected = [
            'root',
            '1',
            '1-1',
            '1-1-1',
            '1-1-1-1',
            '2',
            '2-1',
            '2-2',
        ];
        $this->assertEquals(
            $expected,
            $idList,
            'unexpected id list',
        );
    }

    public function testWalkWithLocationString(): void
    {
        $idList = [];
        $this->walker->walk(
            ResourceLocation::of('/root.php'),
            function ($resource) use (&$idList) {
                $idList[] = $resource->id;
            },
        );

        $expected = [
            'root',
            '1',
            '1-1',
            '1-1-1',
            '1-1-1-1',
            '2',
            '2-1',
            '2-2',
        ];
        $this->assertEquals(
            $expected,
            $idList,
            'unexpected id list',
        );
    }
}
