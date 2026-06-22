<?php

declare(strict_types=1);

namespace Atoolo\Seo\Test\Controller;

use Atoolo\Seo\Controller\SitemapXmlController;
use Atoolo\Seo\Dto\Sitemap\ChangeFreq;
use Atoolo\Seo\Dto\Sitemap\EntriesRequest;
use Atoolo\Seo\Dto\Sitemap\IndexEntry;
use Atoolo\Seo\Dto\Sitemap\IndexRequest;
use Atoolo\Seo\Dto\Sitemap\Link;
use Atoolo\Seo\Dto\Sitemap\UrlEntry;
use Atoolo\Seo\Service\SitemapLoader;
use DateMalformedStringException;
use DateTime;
use DateTimeZone;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(SitemapXmlController::class)]
class SitemapXmlControllerTest extends TestCase
{
    private SitemapLoader $sitemapXmlLoader;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->sitemapXmlLoader = $this->createMock(SitemapLoader::class);
    }

    /**
     * @throws Exception
     */
    private function createController(): SitemapXmlController
    {
        $controller = new SitemapXmlController(
            $this->sitemapXmlLoader,
        );
        $controller->setContainer($this->createStub(ContainerInterface::class));
        return $controller;
    }

    /**
     * @throws Exception|DateMalformedStringException
     */
    public function testSitemapIndex(): void
    {
        $controller = $this->createController();
        $request = $this->createStub(Request::class);
        $request->method('getSchemeAndHttpHost')->willReturn('https://example.com');

        $this->sitemapXmlLoader->method('loadIndex')->willReturn([
            new IndexEntry(
                '/sitemap-1.xml',
                new DateTime('2025-01-24 08:55:00', new DateTimeZone('GMT')),
            ),
            new IndexEntry(
                '/sitemap-2.xml',
                new DateTime('2025-01-24 08:56:00', new DateTimeZone('GMT')),
            ),
        ]);

        $response = $controller->sitemapIndex($request, '', '');

        ob_start();
        $response->sendContent();
        $contents = ob_get_clean();

        $expected = <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <sitemapindex xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
         <sitemap>
          <loc>https://example.com/sitemap-1.xml</loc>
          <lastmod>2025-01-24T08:55:00Z</lastmod>
         </sitemap>
         <sitemap>
          <loc>https://example.com/sitemap-2.xml</loc>
          <lastmod>2025-01-24T08:56:00Z</lastmod>
         </sitemap>
        </sitemapindex>

        XML;

        $this->assertEquals($expected, $contents, "unexpected response");
    }

    /**
     * @throws Exception
     */
    public function testSitemapIndexWithSiteExcludesParameter(): void
    {
        $controller = $this->createController();
        $request = $this->createStub(Request::class);

        $this->sitemapXmlLoader->expects($this->once())
            ->method('loadIndex')
            ->with(new IndexRequest(['12','13'], []))
            ->willReturn([]);

        $controller->sitemapIndex($request, '12,13', '');
    }

    /**
     * @throws Exception
     */
    public function testSitemapIndexWithSiteIncludesParameter(): void
    {
        $controller = $this->createController();
        $request = $this->createStub(Request::class);

        $this->sitemapXmlLoader->expects($this->once())
            ->method('loadIndex')
            ->with(new IndexRequest([], ['12','13']))
            ->willReturn([]);

        $controller->sitemapIndex($request, '', '12,13');
    }

    /**
     * @throws Exception|DateMalformedStringException
     */
    public function testSitemapEntries(): void
    {
        $controller = $this->createController();
        $request = $this->createStub(Request::class);
        $request->method('getSchemeAndHttpHost')->willReturn('https://example.com');

        $this->sitemapXmlLoader->method('loadIndexEntries')->willReturn([
            new UrlEntry(
                '/a',
                new DateTime('2025-01-24 08:55:00', new DateTimeZone('GMT')),
                ChangeFreq::DAILY,
                1,
                [
                    new Link(
                        'alternate',
                        'https://example.com/en/a',
                        'en',
                    ),
                ],
            ),
            new UrlEntry(
                '/b',
                new DateTime('2025-01-24 08:56:00', new DateTimeZone('GMT')),
                ChangeFreq::DAILY,
                1,
                [
                    new Link(
                        'alternate',
                        'https://example.com/en/b',
                        'en',
                    ),
                ],
            ),
        ]);

        $response = $controller->sitemapEntries($request, 1, '', '');

        ob_start();
        $response->sendContent();
        $contents = ob_get_clean();

        $expected = <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" xmlns:video="http://www.google.com/schemas/sitemap-video/1.1">
         <url>
          <loc>https://example.com/a</loc>
          <lastmod>2025-01-24T08:55:00Z</lastmod>
          <xhtml:link rel="alternate" hreflang="https://example.com/en/a" href="https://example.comen"/>
         </url>
         <url>
          <loc>https://example.com/b</loc>
          <lastmod>2025-01-24T08:56:00Z</lastmod>
          <xhtml:link rel="alternate" hreflang="https://example.com/en/b" href="https://example.comen"/>
         </url>
        </urlset>

        XML;

        $this->assertEquals($expected, $contents, "unexpected response");
    }

    /**
     * @throws Exception
     */
    public function testSitemapEntriesWithSiteExcludesParameter(): void
    {
        $controller = $this->createController();
        $request = $this->createStub(Request::class);

        $this->sitemapXmlLoader->expects($this->once())
            ->method('loadIndexEntries')
            ->with(new EntriesRequest(1, ['12','13'], []))
            ->willReturn([]);

        $controller->sitemapEntries($request, 1, '12,13', '');
    }

    /**
     * @throws Exception
     */
    public function testSitemapEntriesWithSiteIncludesParameter(): void
    {
        $controller = $this->createController();
        $request = $this->createStub(Request::class);

        $this->sitemapXmlLoader->expects($this->once())
            ->method('loadIndexEntries')
            ->with(new EntriesRequest(1, [], ['12','13']))
            ->willReturn([]);

        $controller->sitemapEntries($request, 1, '', '12,13');
    }
}
