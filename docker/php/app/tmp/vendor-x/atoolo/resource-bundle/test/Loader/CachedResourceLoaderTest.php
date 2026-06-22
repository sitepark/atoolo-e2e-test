<?php

declare(strict_types=1);

namespace Atoolo\Resource\Test\Loader;

use Atoolo\Resource\Loader\CachedResourceLoader;
use Atoolo\Resource\Resource;
use Atoolo\Resource\ResourceLoader;
use Atoolo\Resource\ResourceLocation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CachedResourceLoader::class)]
class CachedResourceLoaderTest extends TestCase
{
    public function testLoad(): void
    {
        $location = ResourceLocation::of('test');

        $resource = $this->createStub(Resource::class);
        $loader = $this->createMock(ResourceLoader::class);
        $loader->expects($this->once())
            ->method('load')
            ->with($location)
            ->willReturn($resource);
        $cachedLoader = new CachedResourceLoader($loader);
        $cachedLoader->load($location); // cache warmup

        $this->assertEquals(
            $resource,
            $cachedLoader->load($location),
            'Resource should be loaded from cache',
        );
    }

    public function testExistsUncached(): void
    {
        $location = ResourceLocation::of('test');

        $loader = $this->createMock(ResourceLoader::class);
        $loader->expects($this->once())
            ->method('exists')
            ->with($location)
            ->willReturn(true);
        $cachedLoader = new CachedResourceLoader($loader);

        $this->assertTrue(
            $cachedLoader->exists($location),
            'Resource should be test from cache',
        );
    }

    public function testExistsCached(): void
    {

        $location = ResourceLocation::of('test');
        $resource = $this->createStub(Resource::class);
        $loader = $this->createMock(ResourceLoader::class);
        $loader->expects($this->once())
            ->method('load')
            ->with($location)
            ->willReturn($resource);
        $loader->expects($this->exactly(0))
            ->method('exists')
            ->with($location)
            ->willReturn(true);

        $cachedLoader = new CachedResourceLoader($loader);
        $cachedLoader->load($location); // cache warmup

        $this->assertTrue(
            $cachedLoader->exists($location),
            'Resource should be test from cache',
        );
    }

    public function testCleanup(): void
    {

        $location = ResourceLocation::of('test');
        $resource = $this->createStub(Resource::class);
        $loader = $this->createMock(ResourceLoader::class);
        $loader->expects($this->once())
            ->method('load')
            ->with($location)
            ->willReturn($resource);
        $loader->expects($this->exactly(1))
            ->method('exists')
            ->with($location)
            ->willReturn(false);

        $cachedLoader = new CachedResourceLoader($loader);
        $cachedLoader->load($location); // cache warmup
        $cachedLoader->cleanup();

        $this->assertFalse(
            $cachedLoader->exists($location),
            'Resource should be test from cache',
        );
    }
}
