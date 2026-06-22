<?php

declare(strict_types=1);

namespace Atoolo\Rewrite\Test\Dto;

use Atoolo\Rewrite\Dto\Url;
use Atoolo\Rewrite\Dto\UrlBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UrlBuilder::class)]
class UrlBuilderTest extends TestCase
{
    public function testParseUrl(): void
    {
        $url = Url::builder()->parse('https://user:password@www.example.com/foo/bar?param1=1&param2[0]=2&param3[a]=3#hash')->build();

        $expected = Url::builder()
            ->scheme('https')
            ->user('user')
            ->password('password')
            ->host('www.example.com')
            ->path('/foo/bar')
            ->params([
                'param1' => '1',
                'param2' => ['2'],
                'param3' => ['a' => '3'],
            ])
            ->fragment('hash')
            ->build();

        $this->assertEquals(
            $expected,
            $url,
            'unexpected url',
        );
    }

    public function testBuildWithUrlObject(): void
    {

        $url = Url::builder()
            ->scheme('https')
            ->user('user')
            ->password('password')
            ->host('www.example.com')
            ->path('/foo/bar')
            ->params([
                'param1' => '1',
                'param2' => ['2'],
                'param3' => ['a' => '3'],
            ])
            ->fragment('hash')
            ->build();

        $clone = Url::builder()
            ->url($url)
            ->path('/foo/bar/baz')
            ->build();

        $expected = Url::builder()
            ->scheme('https')
            ->user('user')
            ->password('password')
            ->host('www.example.com')
            ->path('/foo/bar/baz')
            ->params([
                'param1' => '1',
                'param2' => ['2'],
                'param3' => ['a' => '3'],
            ])
            ->fragment('hash')
            ->build();

        $this->assertEquals(
            $expected,
            $clone,
            'unexpected url',
        );
    }

    public function testResetByNullQuery(): void
    {
        $url = Url::builder()
            ->parse('https://www.example.com/?param1=1')
            ->query(null)
            ->build();
        $this->assertNull(
            $url->params,
            'params should be null',
        );
    }

    public function testAddParam(): void
    {
        $url = Url::builder()
            ->parse('https://www.example.com/')
            ->param('param', 'value')
            ->build();
        $this->assertEquals(
            ['param' => 'value'],
            $url->params,
            'unexpected params',
        );
    }

}
