<?php

declare(strict_types=1);

namespace Atoolo\Resource\Test;

use Atoolo\Resource\ResourceLanguage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ResourceLanguage::class)]
class ResourceLanguageTest extends TestCase
{
    public function testDefault(): void
    {
        $language = ResourceLanguage::default();
        $this->assertSame('', $language->code);
    }

    public function testEqualsDefault(): void
    {
        $lang = ResourceLanguage::of('');
        $this->assertSame(
            ResourceLanguage::default(),
            $lang,
            'empty lang should be equals to default lang',
        );
    }

    public function testOfOnlyLang(): void
    {
        $language = ResourceLanguage::of('en');
        $this->assertSame('en', $language->code);
    }

    public function testOfWithLocale(): void
    {
        $language = ResourceLanguage::of('en_US');
        $this->assertSame('en', $language->code);
    }

    public function testOfWithISO639part1(): void
    {
        $language = ResourceLanguage::of('de-at');
        $this->assertSame('de', $language->code);
    }

    public function testOfWithNull(): void
    {
        $language = ResourceLanguage::of(null);
        $this->assertSame('', $language->code);
    }

    public function testOfWithEmptyString(): void
    {
        $language = ResourceLanguage::of('');
        $this->assertSame('', $language->code);
    }

    /*
    public function testOfWithISO639part2(): void
    {
        $language = ResourceLanguage::of('ger');
        $this->assertSame('de', $language->code);
    }
    */
}
