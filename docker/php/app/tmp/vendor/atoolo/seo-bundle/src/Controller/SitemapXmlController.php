<?php

declare(strict_types=1);

namespace Atoolo\Seo\Controller;

use Atoolo\Seo\Dto\Sitemap\EntriesRequest;
use Atoolo\Seo\Dto\Sitemap\IndexEntry;
use Atoolo\Seo\Dto\Sitemap\IndexRequest;
use Atoolo\Seo\Dto\Sitemap\UrlEntry;
use Atoolo\Seo\Service\SitemapLoader;
use Locale;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Attribute\MapQueryParameter;
use Symfony\Component\Routing\Attribute\Route;
use XMLWriter;

/**
 * https://developers.google.com/search/docs/crawling-indexing/sitemaps/overview?hl=de
 */
class SitemapXmlController extends AbstractController
{
    public function __construct(
        private readonly SitemapLoader $sitemapXmlLoader,
    ) {}

    #[Route("/sitemap.xml", name: "atoolo_seo_sitemap_xml_index", methods: ['GET'])]
    public function sitemapIndex(
        Request $request,
        #[MapQueryParameter('siteExcludes')]
        string $siteExcludes = '',
        #[MapQueryParameter('siteIncludes')]
        string $siteIncludes = '',
    ): Response {
        $indexRequest = new IndexRequest(
            siteExcludes: $this->parseIntList($siteExcludes),
            siteIncludes: $this->parseIntList($siteIncludes),
        );

        $baseUrl = $request->getSchemeAndHttpHost();

        // https://developers.google.com/search/docs/crawling-indexing/sitemaps/large-sitemaps?hl=de
        $indexes = $this->sitemapXmlLoader->loadIndex($indexRequest);

        $response = new StreamedResponse(function () use ($baseUrl, $indexes) {


            $xmlWriter = new XMLWriter();
            $xmlWriter->openURI('php://output');
            $xmlWriter->startDocument('1.0', 'UTF-8');
            $xmlWriter->setIndent(true);

            $xmlWriter->startElement('sitemapindex');
            $xmlWriter->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

            foreach ($indexes as $index) {
                $this->writeIndexEntry($xmlWriter, $baseUrl, $index);
            }

            $xmlWriter->endElement();
            $xmlWriter->endDocument();

            $xmlWriter->flush();
        });

        $response->headers->set('Content-Type', 'application/xml; charset=UTF-8');

        return $response;
    }

    private function writeIndexEntry(XMLWriter $xmlWriter, string $baseUrl, IndexEntry $index): void
    {
        $xmlWriter->startElement('sitemap');
        $xmlWriter->writeElement('loc', $baseUrl . $index->loc);
        if ($index->lastMod !== null) {
            $xmlWriter->startElement('lastmod');
            $xmlWriter->text(gmdate('Y-m-d\TH:i:s\Z', $index->lastMod->getTimestamp()));
            $xmlWriter->endElement();
        }
        $xmlWriter->endElement();
    }

    #[Route("/sitemap-{page}.xml", name: "atoolo_seo_sitemap_xml_entries", methods: ['GET'])]
    public function sitemapEntries(
        Request $request,
        int $page,
        #[MapQueryParameter('siteExcludes')]
        string $siteExcludes = '',
        #[MapQueryParameter('siteIncludes')]
        string $siteIncludes = '',
    ): Response {

        $entriesRequest = new EntriesRequest(
            page: $page,
            siteExcludes: $this->parseIntList($siteExcludes),
            siteIncludes: $this->parseIntList($siteIncludes),
        );

        $baseUrl = $request->getSchemeAndHttpHost();

        $entries = $this->sitemapXmlLoader->loadIndexEntries($entriesRequest);

        $response = new StreamedResponse(function () use ($baseUrl, $entries) {
            $xmlWriter = new XMLWriter();
            $xmlWriter->openURI('php://output');
            $xmlWriter->startDocument('1.0', 'UTF-8');
            $xmlWriter->setIndent(true);

            $xmlWriter->startElement('urlset');
            $xmlWriter->writeAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');
            $xmlWriter->writeAttribute('xmlns:xhtml', 'http://www.w3.org/1999/xhtml');
            $xmlWriter->writeAttribute('xmlns:image', 'http://www.google.com/schemas/sitemap-image/1.1');
            $xmlWriter->writeAttribute('xmlns:video', 'http://www.google.com/schemas/sitemap-video/1.1');

            foreach ($entries as $entry) {
                $this->writeEntry($xmlWriter, $baseUrl, $entry);
            }

            $xmlWriter->endElement();
            $xmlWriter->endDocument();
            $xmlWriter->flush();
        });

        $response->headers->set('Content-Type', 'application/xml; charset=UTF-8');

        return $response;
    }

    private function writeEntry(XMLWriter $xmlWriter, string $baseUrl, UrlEntry $entry): void
    {
        $xmlWriter->startElement('url');
        $xmlWriter->writeElement('loc', $baseUrl . $entry->loc);
        if ($entry->lastMod !== null) {
            $lastmod = gmdate('Y-m-d\TH:i:s\Z', $entry->lastMod->getTimestamp());
            $xmlWriter->startElement('lastmod');
            $xmlWriter->text($lastmod);
            $xmlWriter->endElement();
        }

        foreach ($entry->links as $link) {
            $xmlWriter->startElementNs('xhtml', 'link', null);
            $xmlWriter->writeAttribute('rel', $link->rel);
            $xmlWriter->writeAttribute('hreflang', $link->hrefLang);
            $xmlWriter->writeAttribute('href', $baseUrl . $link->href);
            $xmlWriter->endElement();
        }

        $xmlWriter->endElement();
    }

    /**
     * @param string $list comma separated list
     * @return array<string>
     */
    private function parseIntList(string $list): array
    {
        if (empty($list)) {
            return [];
        }
        return explode(',', $list);
    }
}
