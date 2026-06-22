<?php

declare(strict_types=1);

namespace Atoolo\CityGov\Service\Indexer\Enricher\SiteKitSchema2x;

use Atoolo\Resource\Resource;
use Atoolo\Search\Service\Indexer\IndexDocument;
use Atoolo\Search\Service\Indexer\IndexSchema2xDocument;

trait AddAlternativeDocumentsTrait
{
    /**
     * @template E of IndexSchema2xDocument
     * @param E $originDocument
     */
    protected function addAlternativeDocuments(
        Resource $resource,
        IndexDocument $originDocument,
    ): void {
        // first: check publisher attribute 'sp_vv_alternativeTitle'
        if ($this->channelAttributes->addAlternativeDocuments !== true) {
            return;
        }

        $alternativeTitleList = $resource->data->getArray(
            'metadata.' . $resource->objectType . '.alternativeNameList',
        );
        if (count($alternativeTitleList) > 0) {
            $updater = $this->solrIndexService->updater($resource->lang);
            for ($i = 0; $i < count($alternativeTitleList); $i++) {
                $alternativeDocument = clone($originDocument);
                /** @var string $name */
                $name = $alternativeTitleList[$i];
                $this->enrichName($name, $alternativeDocument);
                $alternativeDocument->keywords = null;
                $alternativeDocument->description = null;
                $alternativeDocument->content = null;
                $alternativeDocument->id = $originDocument->id . '-' . $i;
                $alternativeDocument->url = $originDocument->url . '?cg_at_id=' . $i;
                $updater->addDocument($alternativeDocument);
            }
            $updater->update();
        }
    }

    /**
     * @template E of IndexSchema2xDocument
     * @param ?string $name
     * @param E $document
     */
    protected function enrichName(?string $name, IndexDocument $document): void
    {
        if (!empty($name)) {
            $document->sp_name = $name;
            $document->sp_title = $name;
            $document->title = $name;
            $name = str_replace(
                ["ä", "ö", "ü", "Ä", "Ö", "Ü"],
                ["ae", "oe", "ue", "Ae", "Oe", "Ue"],
                $name,
            );
            $document->sp_citygov_startletter = mb_substr($name, 0, 1);
            $document->sp_startletter = mb_substr($name, 0, 1);
            $document->sp_sortvalue = $name;
        }
    }
}
