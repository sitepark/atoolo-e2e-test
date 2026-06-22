<?php

declare(strict_types=1);

namespace Atoolo\CityGov\Service\Indexer\Enricher\SiteKitSchema2x;

use Atoolo\CityGov\ChannelAttributes;
use Atoolo\Resource\Resource;
use Atoolo\Resource\ResourceLoader;
use Atoolo\Resource\ResourceLocation;
use Atoolo\Search\Exception\DocumentEnrichingException;
use Atoolo\Search\Service\Indexer\ContentCollector;
use Atoolo\Search\Service\Indexer\DocumentEnricher;
use Atoolo\Search\Service\Indexer\IndexDocument;
use Atoolo\Search\Service\Indexer\IndexSchema2xDocument;
use Atoolo\Search\Service\Indexer\SiteKit\RichtTextMatcher;
use Atoolo\Search\Service\Indexer\SolrIndexService;
use Exception;

/**
 * @phpstan-type Responsibility array{
 *       primary?: bool,
 *       organisation?:array{
 *           url?:string
 *       }
 *   }
 * @implements DocumentEnricher<IndexSchema2xDocument>
 */
class ProductDocumentEnricher implements DocumentEnricher
{
    use AddAlternativeDocumentsTrait;

    public function __construct(
        private readonly ChannelAttributes $channelAttributes,
        private readonly SolrIndexService $solrIndexService,
        private readonly ResourceLoader $resourceLoader,
        private readonly OrganisationDocumentEnricher $organisationEnricher,
    ) {}

    public function cleanup(): void
    {
        $this->resourceLoader->cleanup();
    }

    /**
     * @template E of IndexSchema2xDocument
     * @param Resource $resource
     * @param E $doc
     * @param string $processId
     * @return E
     * @throws DocumentEnrichingException
     */
    public function enrichDocument(
        Resource $resource,
        IndexDocument $doc,
        string $processId,
    ): IndexDocument {

        if ($resource->objectType !== 'citygovProduct') {
            return $doc;
        }

        return $this->enrichDocumentForProduct($resource, $doc);
    }

    /**
     * @template E of IndexSchema2xDocument
     * @param Resource $resource
     * @param E $doc
     * @return E
     * @throws DocumentEnrichingException
     */
    private function enrichDocumentForProduct(
        Resource $resource,
        IndexDocument $doc,
    ): IndexDocument {

        $this->enrichName($resource->data->getString('metadata.citygovProduct.name'), $doc);
        /** @var string[] $leikaNumbers */
        $leikaNumbers = $resource->data->getArray('metadata.citygovProduct.leikaKeys');
        $this->enrichLeikaNumber($leikaNumbers, $doc);
        $this->enrichOrganisationPath($resource, $doc);
        $this->enrichOnlineServices(
            $resource->data->getArray('metadata.citygovProduct.onlineServices.serviceList'),
            $doc,
        );
        $this->addAlternativeDocuments($resource, $doc);

        /** @var string[] $synonymList */
        $synonymList = $resource->data->getArray('metadata.citygovProduct.synonymList');
        $doc->keywords = array_merge($doc->keywords ?? [], $synonymList);

        $this->enrichContent($resource, $doc);

        return $doc;
    }

    /**
     * @template E of IndexSchema2xDocument
     * @param array<mixed> $onlineServiceList
     * @param E $doc
     * @return void
     */
    private function enrichOnlineServices(array $onlineServiceList, IndexDocument $doc): void
    {
        if (!empty($onlineServiceList)) {
            $doc->sp_contenttype = array_merge(
                $doc->sp_contenttype ?? [],
                ['citygovOnlineService'],
            );
        }
    }

    /**
     * @template E of IndexSchema2xDocument
     * @phpstan-param array<string> $leikaKeys
     * @param E $doc
     */
    private function enrichLeikaNumber(array $leikaKeys, IndexDocument $doc): void
    {
        if (!empty($leikaKeys)) {
            $doc->setMetaString('leikanumber', $leikaKeys);
        }
    }

    /**
     * @template E of IndexSchema2xDocument
     * @param Resource $resource
     * @param E $doc
     * @return void
     */
    private function enrichContent(
        Resource $resource,
        IndexDocument $doc,
    ): void {

        $contentCollector = new ContentCollector([
            new RichtTextMatcher(),
        ]);

        $content = $contentCollector->collect(
            $resource->data->getArray(
                'metadata.citygovProduct.content',
            ),
            $resource,
        );
        $cleanContent = preg_replace(
            '/\s+/',
            ' ',
            $content,
        );

        $doc->content = trim(
            ($doc->content ?? '') . ' ' . $cleanContent,
        );
    }

    /**
     * @template E of IndexSchema2xDocument
     * @param E $doc
     * @throws DocumentEnrichingException
     */
    private function enrichOrganisationPath(
        Resource $resource,
        IndexDocument $doc,
    ): void {

        /** @var Responsibility[] $responsibilityList */
        $responsibilityList = $resource->data->getAssociativeArray(
            'metadata.citygovProduct.responsibilityList.items',
        );
        try {
            foreach ($responsibilityList as $responsibility) {
                if (($responsibility['primary'] ?? false) !== true) {
                    continue;
                }
                $primaryOrganisationLocation =
                    $responsibility['organisation']['url']
                    ?? null;
                if ($primaryOrganisationLocation === null) {
                    continue;
                }

                $primaryOrganisationResource = $this->resourceLoader->load(
                    ResourceLocation::of(
                        $primaryOrganisationLocation,
                        $resource->lang,
                    ),
                );
                $this->organisationEnricher->enrichOrganisationPath(
                    $primaryOrganisationResource,
                    $doc,
                );
                break;
            }
        } catch (Exception $e) {
            throw new DocumentEnrichingException(
                $resource->toLocation(),
                'Unable to enrich organisation_path for product',
                0,
                $e,
            );
        }
    }
}
