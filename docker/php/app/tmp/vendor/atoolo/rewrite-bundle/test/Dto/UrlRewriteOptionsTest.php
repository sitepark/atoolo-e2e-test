<?php

declare(strict_types=1);

namespace Atoolo\Rewrite\Test\Dto;

use Atoolo\Rewrite\Dto\UrlRewriteOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UrlRewriteOptions::class)]
class UrlRewriteOptionsTest extends TestCase
{
    public function testNone(): void
    {
        $options = UrlRewriteOptions::none();
        self::assertFalse($options->toFullyQualifiedUrl);
    }

    public function testBuilder(): void
    {
        self::assertNotNull(UrlRewriteOptions::builder());
    }
}
