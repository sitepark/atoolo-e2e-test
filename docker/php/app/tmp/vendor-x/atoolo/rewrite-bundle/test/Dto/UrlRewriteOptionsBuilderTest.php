<?php

declare(strict_types=1);

namespace Atoolo\Rewrite\Test\Dto;

use Atoolo\Rewrite\Dto\UrlRewriteOptionsBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UrlRewriteOptionsBuilder::class)]
class UrlRewriteOptionsBuilderTest extends TestCase
{
    public function testToFullyQualifiedUrl(): void
    {
        $options = (new UrlRewriteOptionsBuilder())->toFullyQualifiedUrl(true)->build();

        $this->assertTrue(
            $options->toFullyQualifiedUrl,
            'unexpected toFullyQualifiedUrl',
        );
    }

    public function testLang(): void
    {
        $options = (new UrlRewriteOptionsBuilder())->lang('en')->build();

        $this->assertEquals(
            'en',
            $options->lang,
            'unexpected lang',
        );
    }
}
