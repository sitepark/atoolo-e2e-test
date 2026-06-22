<?php

declare(strict_types=1);

namespace Atoolo\EventsCalendar\Service\Indexer\SiteKit;

use Atoolo\EventsCalendar\Dto\Indexer\RceEventIndexerInstance;
use Atoolo\EventsCalendar\Dto\Indexer\RceEventIndexerParameter;
use Atoolo\EventsCalendar\Dto\RceEvent\RceEventDate;
use Atoolo\EventsCalendar\Dto\RceEvent\RceEventListItem;
use Atoolo\EventsCalendar\Service\Indexer\RceEventDocumentEnricher;
use Atoolo\Resource\ResourceHierarchyLoader;
use Atoolo\Resource\ResourceHierarchyWalker;
use Atoolo\Resource\ResourceLocation;
use Atoolo\Search\Service\Indexer\IndexDocument;
use Atoolo\Search\Service\Indexer\IndexSchema2xDocument;

/**
 * @implements RceEventDocumentEnricher<IndexSchema2xDocument>
 */
class DefaultSchema2xRceEventCategoryDocumentEnricher implements
    RceEventDocumentEnricher
{
    /**
     * @var array<string,array<string,string>>
     */
    private array $collectChildIdNameMapCache = [];

    public function __construct(
        private readonly ResourceHierarchyLoader $categoryLoader,
    ) {}

    public function cleanup(): void
    {
        $this->categoryLoader->cleanup();
        $this->collectChildIdNameMapCache = [];
    }

    public function enrichDocument(
        RceEventIndexerParameter $parameter,
        RceEventIndexerInstance $instance,
        RceEventListItem $event,
        RceEventDate $eventDate,
        IndexDocument $doc,
        string $processId,
    ): IndexDocument {

        if ($instance->kickerCategoryResourceLocation !== null && !empty($doc->sp_category)) {
            $kickerCategoryChildIdNameMap = $this->collectChildIdNameMap(
                ResourceLocation::of($instance->kickerCategoryResourceLocation),
            );
            $kickerStrings = [];
            foreach ($doc->sp_category as $category) {
                if (isset($kickerCategoryChildIdNameMap[$category])) {
                    $kickerStrings[] = $kickerCategoryChildIdNameMap[$category];
                }
            }
            if (!empty($kickerStrings)) {
                $kicker =  implode(' | ', $kickerStrings);
                $doc->setMetaString('kicker', $kicker);
                // also add kicker to content to it's searchable
                $doc->content = ($doc->content ?? '') . ' ' . $kicker;
            }
        }

        return $doc;
    }

    /**
     * collects all children of a resource (recursively!) and creates a map,
     * mapping their id to their name
     *
     * @return array<string,string>
     */
    private function collectChildIdNameMap(
        ResourceLocation $root,
    ): array {
        if (!isset($this->collectChildIdNameMapCache[$root->location])) {
            /** @var array<string,string> $childIdNameMap */
            $childIdNameMap = [];
            $walker = new ResourceHierarchyWalker($this->categoryLoader);
            $walker->walk(
                $root,
                function ($resource) use (&$childIdNameMap) {
                    $childIdNameMap[$resource->id] = $resource->data->getString('base.title');
                },
            );
            $this->collectChildIdNameMapCache[$root->location] = $childIdNameMap;
        }
        return $this->collectChildIdNameMapCache[$root->location];
    }
}
