<?php

declare(strict_types=1);

namespace Atoolo\Seo\Service;

use Atoolo\Resource\ResourceChannel;
use Atoolo\Rewrite\Dto\UrlRewriteOptions;
use Atoolo\Rewrite\Dto\UrlRewriteType;
use Atoolo\Rewrite\Service\UrlRewriter;
use Atoolo\Search\Dto\Search\Query\Filter\ContentTypeFilter;
use Atoolo\Search\Dto\Search\Query\Filter\Filter;
use Atoolo\Search\Dto\Search\Query\Filter\NotFilter;
use Atoolo\Search\Dto\Search\Query\Filter\SiteFilter;
use Atoolo\Search\Dto\Search\Query\Filter\SourceFilter;
use Atoolo\Search\Dto\Search\Query\SearchQueryBuilder;
use Atoolo\Search\Search;
use Atoolo\Seo\Dto\Sitemap\ChangeFreq;
use Atoolo\Seo\Dto\Sitemap\EntriesRequest;
use Atoolo\Seo\Dto\Sitemap\IndexEntry;
use Atoolo\Seo\Dto\Sitemap\IndexRequest;
use Atoolo\Seo\Dto\Sitemap\Link;
use Atoolo\Seo\Dto\Sitemap\UrlEntry;
use Locale;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class SitemapLoader
{
    private readonly int $languageCount;

    private readonly int $maxUrlsPerSitemap;

    public function __construct(
        #[Autowire(service: 'atoolo_search.search')]
        private readonly Search $search,
        #[Autowire(service: 'atoolo_resource.resource_channel')]
        private readonly ResourceChannel $channel,
        #[Autowire(service: 'atoolo_seo.sitemap.url_rewriter')]
        private readonly UrlRewriter $urlRewriter,
        #[Autowire(param: 'atoolo_seo.sitemap.max_urls_per_sitemap')]
        int $maxUrlsPerSitemap,
    ) {
        /*
         * maxUrlsPerSitemap must be a multiple of all possible languages,
         * as entries for each language are returned for each hit.
         */
        $this->languageCount = count($this->channel->translationLocales) + 1;
        $this->maxUrlsPerSitemap = $maxUrlsPerSitemap - ($maxUrlsPerSitemap % $this->languageCount);
    }

    /**
     * @return array<IndexEntry>
     */
    public function loadIndex(IndexRequest $request): array
    {
        $builder = new SearchQueryBuilder();
        $builder->filter(...$this->getFilter($request->siteExcludes, $request->siteIncludes));
        $builder->limit(0);

        $result = $this->search->search($builder->build());
        $limit = $this->maxUrlsPerSitemap / $this->languageCount;
        $pages = ceil($result->total / $limit);

        $indexes = [];
        foreach (range(1, $pages) as $page) {
            $loc = '/sitemap-' . $page . '.xml';
            if (!empty($request->siteExcludes)) {
                $loc .= '?siteExcludes=' . implode(',', $request->siteExcludes);
            } elseif (!empty($request->siteIncludes)) {
                $loc .= '?siteIncludes=' . implode(',', $request->siteIncludes);
            }
            $indexes[] = new IndexEntry($loc);
        }

        return $indexes;
    }

    /**
     * @return array<UrlEntry>
     */
    public function loadIndexEntries(EntriesRequest $request): array
    {
        $builder = new SearchQueryBuilder();
        $builder->filter(...$this->getFilter($request->siteExcludes, $request->siteIncludes));
        $limit = $this->maxUrlsPerSitemap / $this->languageCount;
        $builder->offset(($request->page - 1) * $limit);
        $builder->limit($limit);

        $result = $this->search->search($builder->build());

        $entries = [];
        $locales = array_merge([''], $this->channel->translationLocales);

        foreach ($result->results as $item) {
            foreach ($locales as $locale) {
                $entries[] = $this->buildEntry(
                    $item->location,
                    $locale,
                    $locales,
                );
            }
        }

        return $entries;
    }

    /**
     * @param array<string> $locales
     */
    private function buildEntry(string $path, string $locale, array $locales): UrlEntry
    {
        $links = $this->getLinksToTranslations($path, $locale, $locales);
        return new UrlEntry(
            loc: $this->buildUrl($locale, $path),
            lastMod: null,
            changeFreq: ChangeFreq::DAILY,
            priority: 1,
            links: $links,
        );
    }

    /**
     * @param array<string> $locales
     * @return array<Link>
     */
    private function getLinksToTranslations(string $path, string $locale, array $locales): array
    {
        $links = [];
        foreach ($locales as $alternativeLocale) {
            if ($alternativeLocale === $locale) {
                continue;
            }
            $links[] = $this->createLink('alternate', $path, $alternativeLocale);
        }

        return $links;
    }

    private function createLink(
        string $rel,
        string $path,
        string $locale,
    ): Link {
        $code = Locale::getPrimaryLanguage(
            $locale !== ''
                ? $locale
                : $this->channel->locale,
        ) ?? 'de';

        return new Link(
            rel: $rel,
            hrefLang: $code,
            href: $this->buildUrl($locale, $path),
        );
    }

    private function buildUrl(string $locale, string $path): string
    {
        $code = $locale === '' ? '' : Locale::getPrimaryLanguage($locale);
        $url = ($code === '' ? '' : ('/' . $code)) . $path;

        return $this->urlRewriter->rewrite(UrlRewriteType::LINK, $url, UrlRewriteOptions::none());
    }

    /**
     * @param array<string> $siteExcludes
     * @param array<string> $siteIncludes
     * @return array<Filter>
     */
    private function getFilter(array $siteExcludes, array $siteIncludes): array
    {
        $filter = [
            new SourceFilter(['internal']),
            new ContentTypeFilter(['text/html*']),
        ];
        if (!empty($siteExcludes)) {
            $filter[] = new NotFilter(new SiteFilter($siteExcludes));
        } elseif (!empty($siteIncludes)) {
            $filter[] = new SiteFilter($siteIncludes);
        }

        return $filter;
    }
}
