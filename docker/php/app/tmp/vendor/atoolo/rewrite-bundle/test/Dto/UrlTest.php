<?php

declare(strict_types=1);

namespace Atoolo\Rewrite\Test\Dto;

use Atoolo\Rewrite\Dto\Url;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Url::class)]
class UrlTest extends TestCase
{
    public function testBuilder(): void
    {
        $this->assertNotNull(Url::builder(), 'builder should not be null');
    }

    public function testToBuilder(): void
    {
        $url = new Url(
            scheme: 'https',
            host: 'www.example.com',
            port: 8080,
            user: 'user',
            password: 'password',
            path: '/foo/bar',
            params: ['param1' => '1', 'param2' => ['2'], 'param3' => ['a' => '3']],
            fragment: 'hash',
            paramEncType: PHP_QUERY_RFC1738,
        );

        $clone = $url->toBuilder()->build();

        $this->assertEquals(
            $url,
            $clone,
            'unexpected url',
        );
    }

    public function testGetBaseName(): void
    {
        $url = $this->createUrlByPath('/foo/bar.html');
        $this->assertEquals(
            'bar.html',
            $url->getBasename(),
            'unexpected basename',
        );
    }

    public function testGetBaseNameWithSlashEnding(): void
    {
        $url = $this->createUrlByPath('/foo/bar/');
        $this->assertEquals(
            'bar',
            $url->getBasename(),
            'unexpected basename',
        );
    }

    public function testGetBaseNameWithNullPath(): void
    {
        $url = $this->createUrlByPath(null);
        $this->assertEquals(
            null,
            $url->getBasename(),
            'basename should be null',
        );
    }


    public function testGetSuffix(): void
    {
        $url = $this->createUrlByPath('/foo/bar.html');
        $this->assertEquals(
            'html',
            $url->getSuffix(),
            'unexpected suffix',
        );
    }

    public function testGetSuffixWithNullPath(): void
    {
        $url = $this->createUrlByPath(null);
        $this->assertNull(
            $url->getSuffix(),
            'suffix should be null',
        );
    }

    public function testGetSuffixWithoutDotInPath(): void
    {
        $url = $this->createUrlByPath('/foo/bar');
        $this->assertNull(
            $url->getSuffix(),
            'suffix should be null',
        );
    }

    public function testIfFullQualified(): void
    {
        $url = $this->createFQUrlByPath(
            scheme: 'https',
            host: 'www.example.com',
            path: '/foo/bar.html',
        );
        $this->assertTrue(
            $url->isFullyQualified(),
            'url should be fully qualified',
        );
    }

    public function testIfNotFullQualified(): void
    {
        $url = $this->createUrlByPath('/foo/bar.html');
        $this->assertFalse(
            $url->isFullyQualified(),
            'url should not be fully qualified',
        );
    }

    public function testIfFullQualifiedIsRelative(): void
    {
        $url = $this->createFQUrlByPath(
            scheme: 'https',
            host: 'www.example.com',
            path: '/foo/bar.html',
        );
        $this->assertFalse(
            $url->isRelative(),
            'url should not be relative',
        );
    }

    public function testIfNullPathIsRelative(): void
    {
        $url = $this->createUrlByPath(null);
        $this->assertFalse(
            $url->isRelative(),
            'url should not be relative',
        );
    }

    public function testIfStartingSlashPathIsRelative(): void
    {
        $url = $this->createUrlByPath('/foo');
        $this->assertFalse(
            $url->isRelative(),
            'url should not be relative',
        );
    }

    public function testIfNonStartingSlashPathIsRelative(): void
    {
        $url = $this->createUrlByPath('foo');
        $this->assertTrue(
            $url->isRelative(),
            'url should be relative',
        );
    }

    public function testToFullyQualified(): void
    {
        $base = $this->createFQUrlByPath('https', 'www.example.com', '/foo');
        $url = $this->createUrlByPath('/bar');

        $expected = $this->createFQUrlByPath('https', 'www.example.com', '/bar');

        $this->assertEquals(
            $expected,
            $base->toFullyQualified($url),
            'url should be full qualified',
        );
    }

    public function testToFullyQualifiedWithNonFQUrlBase(): void
    {
        $base = $this->createUrlByPath('/foo');
        $url = $this->createUrlByPath('/bar');

        $this->expectException(LogicException::class);
        $base->toFullyQualified($url);
    }

    public function testIfUrlAlreadyFullQualified(): void
    {
        $base = $this->createFQUrlByPath('https', 'www.example.com', '/foo');
        $url = $this->createFQUrlByPath('https', 'www.example.com', '/bar');

        $this->assertSame(
            $url,
            $base->toFullyQualified($url),
            'url should be the same',
        );
    }

    public function testToFullyQualifiedWithNullPath(): void
    {
        $base = $this->createFQUrlByPath('https', 'www.example.com', '/foo');
        $url = $this->createUrlByPath(null);

        $expected = $this->createFQUrlByPath('https', 'www.example.com', null);

        $this->assertEquals(
            $expected,
            $base->toFullyQualified($url),
            'url should be full qualified',
        );
    }


    public function testToFullyQualifiedWithRelativePath(): void
    {
        $base = $this->createFQUrlByPath('https', 'www.example.com', '/foo/bar/bax');
        $url = $this->createUrlByPath('.././baz');

        $expected = $this->createFQUrlByPath('https', 'www.example.com', '/foo/baz');

        $this->assertEquals(
            $expected,
            $base->toFullyQualified($url),
            'url should be full qualified',
        );
    }

    public function testToBaseUrl(): void
    {

        $url = new Url(
            scheme: 'https',
            host: 'www.example.com',
            port: 8443,
            user: 'user',
            password: 'password',
            path: '/foo/bar',
            params: null,
            fragment: null,
            paramEncType: PHP_QUERY_RFC1738,
        );

        $this->assertEquals(
            'https://user:password@www.example.com:8443',
            $url->getBaseUrl(),
            'base url should be null',
        );
    }

    public function testWithNullSchemeToBaseUrl(): void
    {
        $url = $this->createUrlByPath('/foo/bar');

        $this->assertNull(
            $url->getBaseUrl(),
            'base url should be null',
        );
    }

    public function testNonNetworkUrlToBaseUrl(): void
    {
        $url = $this->createNonNetworkUrl('tel', '123');

        $this->assertEquals(
            'tel:',
            $url->getBaseUrl(),
            'base url should be null',
        );
    }

    public function testToString(): void
    {

        $url = new Url(
            scheme: 'https',
            host: 'www.example.com',
            port: 8443,
            user: 'user',
            password: 'password',
            path: '/foo/bar',
            params: [
                'param1' => '1',
                'param2' => ['2'],
                'param3' => ['a' => 3],
            ],
            fragment: 'hash',
            paramEncType: PHP_QUERY_RFC1738,
        );

        $this->assertEquals(
            'https://user:password@www.example.com:8443/foo/bar?param1=1&param2%5B0%5D=2&param3%5Ba%5D=3#hash',
            $url . '',
            'unexpected string',
        );
    }

    private function createUrlByPath(?string $path): Url
    {
        return new Url(
            scheme: null,
            host: null,
            port: null,
            user: null,
            password: null,
            path: $path,
            params: null,
            fragment: null,
            paramEncType: PHP_QUERY_RFC1738,
        );
    }

    private function createNonNetworkUrl(string $scheme, ?string $path): Url
    {
        return new Url(
            scheme: $scheme,
            host: null,
            port: null,
            user: null,
            password: null,
            path: $path,
            params: null,
            fragment: null,
            paramEncType: PHP_QUERY_RFC1738,
        );
    }

    private function createFQUrlByPath(string $scheme, string $host, ?string $path): Url
    {
        return new Url(
            scheme: $scheme,
            host: $host,
            port: null,
            user: null,
            password: null,
            path: $path,
            params: null,
            fragment: null,
            paramEncType: PHP_QUERY_RFC1738,
        );
    }

}
