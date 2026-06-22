<?php

declare(strict_types=1);

namespace Atoolo\Seo\Test\Service;

use Atoolo\Resource\DataBag;
use Atoolo\Resource\Resource;
use Atoolo\Resource\ResourceChannel;
use Atoolo\Resource\ResourceLanguage;
use Atoolo\Resource\ResourceTenant;
use Atoolo\Rewrite\Dto\UrlRewriteOptions;
use Atoolo\Rewrite\Dto\UrlRewriteType;
use Atoolo\Rewrite\Service\UrlRewriter;
use Atoolo\Search\Dto\Search\Result\SearchResult;
use Atoolo\Search\Search;
use Atoolo\Seo\Dto\Sitemap\ChangeFreq;
use Atoolo\Seo\Dto\Sitemap\EntriesRequest;
use Atoolo\Seo\Dto\Sitemap\IndexEntry;
use Atoolo\Seo\Dto\Sitemap\IndexRequest;
use Atoolo\Seo\Dto\Sitemap\Link;
use Atoolo\Seo\Dto\Sitemap\UrlEntry;
use Atoolo\Seo\Service\SitemapLoader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

#[CoversClass(SitemapLoader::class)]
class SitemapLoaderTest extends TestCase
{
    private UrlRewriter $rewriter;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->rewriter = $this->createMock(UrlRewriter::class);
    }

    /**
     * @throws Exception
     */
    public function testLoadIndexWithSiteIncludesAndExcludes(): void
    {
        $search = $this->createStub(Search::class);
        $searchResult = new SearchResult(
            total: 1234,
            limit: 0,
            offset: 0,
            results: [],
            facetGroups: [],
            spellcheck: null,
            queryTime: 0,
        );
        $search->method('search')
            ->willReturn($searchResult);
        $channel = $this->createChannel('de_DE', ['en_US', 'it_IT']);
        $loader = new SitemapLoader($search, $channel, $this->rewriter, 1000);

        $request = new IndexRequest(['12', '13'], ['14', '15']);
        $indexes = $loader->loadIndex($request);

        $expected = [
            new IndexEntry('/sitemap-1.xml?siteExcludes=12,13'),
            new IndexEntry('/sitemap-2.xml?siteExcludes=12,13'),
            new IndexEntry('/sitemap-3.xml?siteExcludes=12,13'),
            new IndexEntry('/sitemap-4.xml?siteExcludes=12,13'),
        ];

        $this->assertEquals($expected, $indexes, 'unexpected index entries');
    }

    /**
     * @throws Exception
     */
    public function testLoadIndexWithSiteIncludes(): void
    {
        $search = $this->createStub(Search::class);
        $searchResult = new SearchResult(
            total: 1234,
            limit: 0,
            offset: 0,
            results: [],
            facetGroups: [],
            spellcheck: null,
            queryTime: 0,
        );
        $search->method('search')
            ->willReturn($searchResult);
        $channel = $this->createChannel('de_DE', ['en_US', 'it_IT']);
        $loader = new SitemapLoader($search, $channel, $this->rewriter, 1000);

        $request = new IndexRequest([], ['14', '15']);
        $indexes = $loader->loadIndex($request);

        $expected = [
            new IndexEntry('/sitemap-1.xml?siteIncludes=14,15'),
            new IndexEntry('/sitemap-2.xml?siteIncludes=14,15'),
            new IndexEntry('/sitemap-3.xml?siteIncludes=14,15'),
            new IndexEntry('/sitemap-4.xml?siteIncludes=14,15'),
        ];

        $this->assertEquals($expected, $indexes, 'unexpected index entries');
    }

    /**
     * @throws Exception
     */
    public function testLoadIndexEntriesWithSiteIncludesAndExcludes(): void
    {
        $search = $this->createStub(Search::class);
        $searchResult = new SearchResult(
            total: 5,
            limit: 0,
            offset: 0,
            results: [
                $this->createResource('/a'),
                $this->createResource('/b'),
            ],
            facetGroups: [],
            spellcheck: null,
            queryTime: 0,
        );
        $search->method('search')
            ->willReturn($searchResult);
        $channel = $this->createChannel('de_DE', ['en_US']);
        $this->rewriter->expects($this->exactly(8))
            ->method('rewrite')
            ->willReturnCallback(fn(UrlRewriteType $type, string $url, UrlRewriteOptions $options) => $url);
        $loader = new SitemapLoader($search, $channel, $this->rewriter, 1000);

        $request = new EntriesRequest(1, ['12', '13'], ['14', '15']);
        $indexes = $loader->loadIndexEntries($request);

        $expected = [
            new UrlEntry(
                loc: '/a',
                lastMod: null,
                changeFreq: ChangeFreq::DAILY,
                priority: 1,
                links: [
                    new Link(
                        rel: 'alternate',
                        hrefLang: 'en',
                        href: '/en/a',
                    ),
                ],
            ),
            new UrlEntry(
                loc: '/en/a',
                lastMod: null,
                changeFreq: ChangeFreq::DAILY,
                priority: 1,
                links: [
                    new Link(
                        rel: 'alternate',
                        hrefLang: 'de',
                        href: '/a',
                    ),
                ],
            ),
            new UrlEntry(
                loc: '/b',
                lastMod: null,
                changeFreq: ChangeFreq::DAILY,
                priority: 1,
                links: [
                    new Link(
                        rel: 'alternate',
                        hrefLang: 'en',
                        href: '/en/b',
                    ),
                ],
            ),
            new UrlEntry(
                loc: '/en/b',
                lastMod: null,
                changeFreq: ChangeFreq::DAILY,
                priority: 1,
                links: [
                    new Link(
                        rel: 'alternate',
                        hrefLang: 'de',
                        href: '/b',
                    ),
                ],
            ),
        ];

        $this->assertEquals($expected, $indexes, 'unexpected index entries');
    }

    private function createResource(string $location): Resource
    {
        return new Resource(
            location: $location,
            id: '',
            name: '',
            objectType: '',
            lang: ResourceLanguage::default(),
            data: new DataBag([]),
        );
    }
    /**
     * @throws Exception
     */
    private function createChannel(string $locale, array $translationLocales): ResourceChannel
    {
        return new ResourceChannel(
            id: '',
            name: '',
            anchor: '',
            serverName: '',
            isPreview: false,
            nature: '',
            locale: $locale,
            baseDir: '',
            resourceDir: '',
            configDir: '',
            searchIndex: '',
            translationLocales: $translationLocales,
            tenant: $this->createStub(ResourceTenant::class),
        );
    }
}
