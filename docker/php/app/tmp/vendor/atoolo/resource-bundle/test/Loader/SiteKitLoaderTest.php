<?php

declare(strict_types=1);

namespace Atoolo\Resource\Test\Loader;

use Atoolo\Resource\DataBag;
use Atoolo\Resource\Exception\InvalidResourceException;
use Atoolo\Resource\Exception\ResourceNotFoundException;
use Atoolo\Resource\Loader\SiteKitLoader;
use Atoolo\Resource\ResourceChannel;
use Atoolo\Resource\ResourceLanguage;
use Atoolo\Resource\ResourceLocation;
use Atoolo\Resource\ResourceTenant;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SiteKitLoader::class)]
class SiteKitLoaderTest extends TestCase
{
    private SiteKitLoader $loader;

    protected function setUp(): void
    {
        $resourceDir = realpath(__DIR__ . '/../resources/Loader/SiteKitLoader');
        $channel = new ResourceChannel(
            '1',
            'Test Channel',
            'test-www',
            'test-server',
            false,
            'internet',
            'de_DE',
            '',
            $resourceDir,
            '',
            'test-www',
            ['en_US', 'it_IT'],
            new DataBag(['attribute' => 'value']),
            $this->createStub(ResourceTenant::class),
        );
        $this->loader = new SiteKitLoader($channel);
    }

    public function testExists(): void
    {
        $exists = $this->loader->exists(
            ResourceLocation::of('validResource.php'),
        );
        $this->assertTrue($exists, 'resource should exist');
    }

    public function testExistsWithLang(): void
    {
        $exists = $this->loader->exists(
            ResourceLocation::of('validResource.php'),
        );
        $this->assertTrue($exists, 'resource should exist');
    }

    public function testLoadValidResource(): void
    {
        $resource = $this->loader->load(
            ResourceLocation::of('validResource.php'),
        );
        $this->assertEquals(
            '1118',
            $resource->id,
            'unexpected id',
        );
    }

    public function testLoadValidResourceWithLang(): void
    {
        $resource = $this->loader->load(
            ResourceLocation::of(
                'validResource.php',
                ResourceLanguage::of('en'),
            ),
        );
        $this->assertEquals(
            ResourceLanguage::of('en'),
            $resource->lang,
            'should load en',
        );
    }

    public function testLoadValidResourceWithLangAndFallback(): void
    {
        $resource = $this->loader->load(
            ResourceLocation::of(
                'validResource.php',
                ResourceLanguage::of('it'),
            ),
        );
        $this->assertEquals(
            ResourceLanguage::of('de'),
            $resource->lang,
            'should fallback to de',
        );
    }

    public function testCleanup(): void
    {
        $this->expectNotToPerformAssertions();
        $this->loader->cleanup();
    }

    public function testLoadMissingLocation(): void
    {
        $this->expectException(ResourceNotFoundException::class);
        $this->loader->load(ResourceLocation::of('notfound.php'));
    }

    public function testLoadResourceWithCompileError(): void
    {
        $this->expectException(InvalidResourceException::class);
        $this->loader->load(ResourceLocation::of('compileError.php'));
    }

    public function testLoadResourceWithCommonError(): void
    {
        $this->expectException(InvalidResourceException::class);
        $this->loader->load(ResourceLocation::of('commonError.php'));
    }

    public function testLoadWithMissingInit(): void
    {
        $this->expectException(InvalidResourceException::class);
        $this->loader->load(ResourceLocation::of('missingInitResource.php'));
    }

    public function testLoadWithInitNotAnArray(): void
    {
        $this->expectException(InvalidResourceException::class);
        $this->loader->load(ResourceLocation::of('initNotAnArrayResource.php'));
    }

    public function testLoadWithNonIntId(): void
    {
        $this->expectException(InvalidResourceException::class);
        $this->loader->load(ResourceLocation::of('nonIntIdResource.php'));
    }

    public function testLoadWithMissingId(): void
    {
        $this->expectException(InvalidResourceException::class);
        $this->loader->load(ResourceLocation::of('missingIdResource.php'));
    }

    public function testLoadWithMissingName(): void
    {
        $this->expectException(InvalidResourceException::class);
        $this->loader->load(ResourceLocation::of('missingNameResource.php'));
    }

    public function testLoadWithNonStringName(): void
    {
        $this->expectException(InvalidResourceException::class);
        $this->loader->load(ResourceLocation::of('nonStringNameResource.php'));
    }

    public function testLoadWithMissingObjectType(): void
    {
        $this->expectException(InvalidResourceException::class);
        $this->loader->load(
            ResourceLocation::of('missingObjectTypeResource.php'),
        );
    }

    public function testLoadWithNonStringObjectType(): void
    {
        $this->expectException(InvalidResourceException::class);
        $this->loader->load(
            ResourceLocation::of('nonStringObjectTypeResource.php'),
        );
    }

    public function testLoadWithMissingLocale(): void
    {
        $this->expectException(InvalidResourceException::class);
        $this->loader->load(
            ResourceLocation::of('missingLocaleResource.php'),
        );
    }

    public function testLoadWithNonStringLocale(): void
    {
        $this->expectException(InvalidResourceException::class);
        $this->loader->load(
            ResourceLocation::of('nonStringLocaleResource.php'),
        );
    }

    public function testLoadWithNonArrayReturned(): void
    {
        $this->expectException(InvalidResourceException::class);
        $this->loader->load(ResourceLocation::of('nonArrayReturned.php'));
    }
}
