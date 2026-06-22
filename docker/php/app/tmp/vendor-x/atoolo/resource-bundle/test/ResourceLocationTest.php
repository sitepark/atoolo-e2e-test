<?php

declare(strict_types=1);

namespace Atoolo\Resource\Test;

use Atoolo\Resource\ResourceLanguage;
use Atoolo\Resource\ResourceLocation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ResourceLocation::class)]
class ResourceLocationTest extends TestCase
{
    public function testOf(): void
    {
        $location = ResourceLocation::of('/path');
        $this->assertEquals(
            '/path',
            $location->location,
            'unexpected location',
        );
    }
    public function testOfWithLang(): void
    {
        $location = ResourceLocation::of(
            '',
            ResourceLanguage::of('en'),
        );
        $this->assertEquals(
            ResourceLanguage::of('en'),
            $location->lang,
            'unexpected lang',
        );
    }

    public function testToString(): void
    {
        $location = ResourceLocation::of(
            '/path',
        );
        $this->assertEquals(
            '/path',
            $location->__toString(),
            'unexpected string',
        );
    }

    public function testToStringWithLang(): void
    {
        $location = ResourceLocation::of(
            '/path',
            ResourceLanguage::of('en'),
        );
        $this->assertEquals(
            '/path:en',
            $location->__toString(),
            'unexpected string',
        );
    }

    public function testOfPath(): void
    {
        $location = ResourceLocation::ofPath(
            '/path',
        );
        $this->assertEquals(
            '/path.php',
            $location->__toString(),
            'unexpected string',
        );
    }

    public function testOfPathWithSuffix(): void
    {
        $location = ResourceLocation::ofPath(
            '/path.php',
        );
        $this->assertEquals(
            '/path.php',
            $location->__toString(),
            'unexpected string',
        );
    }

    public function testOfPathWithEndingSlash(): void
    {
        $location = ResourceLocation::ofPath(
            '/path/',
        );
        $this->assertEquals(
            '/path/index.php',
            $location->__toString(),
            'unexpected string',
        );
    }
}
