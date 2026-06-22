<?php

declare(strict_types=1);

namespace Atoolo\CityCall\Service\Indexer\Enricher\SiteKitSchema2x;

use Atoolo\Resource\Resource;
use Atoolo\Search\Exception\DocumentEnrichingException;
use Atoolo\Search\Service\Indexer\DocumentEnricher;
use Atoolo\Search\Service\Indexer\IndexDocument;
use Atoolo\Search\Service\Indexer\IndexSchema2xDocument;

/**
 * @implements DocumentEnricher<IndexSchema2xDocument>
 */
class NewsDocumentEnricher implements DocumentEnricher
{
    public function cleanup(): void {}

    /**
     * @throws DocumentEnrichingException
     */
    public function enrichDocument(
        Resource $resource,
        IndexDocument $doc,
        string $processId,
    ): IndexSchema2xDocument {

        if ($resource->objectType !== 'citycall-news') {
            return $doc;
        }

        return $this->enrichDocumentForNews($resource, $doc);
    }

    /**
     * @template E of IndexSchema2xDocument
     * @param E $doc
     * @return E
     */
    private function enrichDocumentForNews(
        Resource $resource,
        IndexDocument $doc,
    ): IndexDocument {

        $isAdHocActive = $resource->data->getBool(
            'metadata.isAdHocActive',
        );
        if ($isAdHocActive) {
            $doc->setMetaBool('citycall_isadhoc', true);
        }

        return $doc;
    }
}
