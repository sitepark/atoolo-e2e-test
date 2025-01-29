<?php

declare(strict_types=1);

namespace Atoolo\E2E\Test\Seo;

use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class SitemapXmlTest extends TestCase
{
    private HttpClientInterface $client;

    public function setUp(): void
    {
        $this->client = HttpClient::createForBaseUri($_SERVER['ENDPOINT_BASE']);
    }

    public function testGetSitemapXmlIndex(): void
    {

        $response = $this->client->request(
            'GET',
            'sitemap.xml',
        );

        $expected = str_replace(
            '__ENDPOINT_BASE__',
            $_SERVER['ENDPOINT_BASE'],
            file_get_contents(__DIR__ . '/resources/sitemap.xml'),
        );

        $this->assertEquals(
            $expected,
            $response->getContent(),
            'Sitemap XML does not match',
        );
    }

    public function testGetSitemapXmlUrlSet(): void
    {

        $response = $this->client->request(
            'GET',
            'sitemap-1.xml',
        );

        $expected = str_replace(
            '__ENDPOINT_BASE__',
            $_SERVER['ENDPOINT_BASE'],
            file_get_contents(__DIR__ . '/resources/sitemap-1.xml'),
        );

        $this->assertEquals(
            $expected,
            $response->getContent(),
            'Sitemap XML does not match',
        );
    }

}
