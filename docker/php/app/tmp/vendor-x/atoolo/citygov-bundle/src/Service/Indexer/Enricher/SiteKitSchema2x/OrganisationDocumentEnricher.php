<?php

declare(strict_types=1);

namespace Atoolo\CityGov\Service\Indexer\Enricher\SiteKitSchema2x;

use Atoolo\CityGov\ChannelAttributes;
use Atoolo\Resource\Loader\SiteKitResourceHierarchyLoader;
use Atoolo\Resource\Resource;
use Atoolo\Search\Exception\DocumentEnrichingException;
use Atoolo\Search\Service\Indexer\DocumentEnricher;
use Atoolo\Search\Service\Indexer\IndexDocument;
use Atoolo\Search\Service\Indexer\IndexSchema2xDocument;
use Atoolo\Search\Service\Indexer\SolrIndexService;
use Exception;

/**
 * TODO sp_vv_alternativeTitle -> https://gitlab.sitepark.com/sitekit/citygov-php/-/blob/develop/php/SP/CityGov/Component/Initialization.php?ref_type=heads#L122
 * @implements DocumentEnricher<IndexSchema2xDocument>
 */
class OrganisationDocumentEnricher implements DocumentEnricher
{
    use AddAlternativeDocumentsTrait;

    public function __construct(
        private readonly ChannelAttributes $channelAttributes,
        private readonly SolrIndexService $solrIndexService,
        private readonly SiteKitResourceHierarchyLoader $hierarchyLoader,
    ) {}

    public function cleanup(): void
    {
        $this->hierarchyLoader->cleanup();
    }

    /**
     * @throws DocumentEnrichingException
     */
    public function enrichDocument(
        Resource $resource,
        IndexDocument $doc,
        string $processId,
    ): IndexDocument {

        if ($resource->objectType !== 'citygovOrganisation') {
            return $doc;
        }

        return $this->enrichDocumentForOrganisation($resource, $doc);
    }

    /**
     * @template E of IndexSchema2xDocument
     * @param E $doc
     * @return E
     */
    private function enrichDocumentForOrganisation(
        Resource $resource,
        IndexDocument $doc,
    ): IndexDocument {
        $this->enrichName($resource->data->getString('metadata.citygovOrganisation.name'), $doc);
        $doc = $this->enrichOrganisationPath($resource, $doc);
        $this->addAlternativeDocuments($resource, $doc);

        /** @var string[] $synonymList */
        $synonymList = $resource->data->getArray('metadata.citygovOrganisation.synonymList');
        $doc->keywords = array_merge($doc->keywords ?? [], $synonymList);

        $doc->sp_citygov_organisationtoken = [$resource->data->getString(
            'metadata.citygovOrganisation.token',
        )];
        $content = array_merge(
            [$doc->content ?? ''],
            $doc->sp_citygov_organisationtoken,
        );
        $doc->content = trim(implode(' ', $content));

        return $doc;
    }

    /**
     * @template E of IndexSchema2xDocument
     * @param E $doc
     * @return E
     * @throws DocumentEnrichingException
     */
    public function enrichOrganisationPath(
        Resource $resource,
        IndexDocument $doc,
    ): IndexDocument {
        $doc->sp_organisation = (int) $resource->id;

        try {
            $organisationPath =
                $this->hierarchyLoader->loadPrimaryPath(
                    $resource->toLocation(),
                );
            $organisationIdPath = array_map(static function ($resource) {
                return (int) $resource->id;
            }, $organisationPath);
            $doc->sp_organisation_path = array_merge(
                $doc->sp_organisation_path ?? [],
                $organisationIdPath,
            );
        } catch (Exception $e) {
            throw new DocumentEnrichingException(
                $resource->toLocation(),
                'Unable to enrich sp_organisation_path',
                0,
                $e,
            );
        }
        return $doc;
    }
}
