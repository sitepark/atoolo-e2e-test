<?php

declare(strict_types=1);

namespace Atoolo\EventsCalendar\Service\Indexer\SiteKit;

use Atoolo\Resource\Resource;
use Atoolo\Search\Exception\DocumentEnrichingException;
use Atoolo\Search\Service\Indexer\DocumentEnricher;
use Atoolo\Search\Service\Indexer\IndexDocument;
use Atoolo\Search\Service\Indexer\IndexSchema2xDocument;

/**
 * @implements DocumentEnricher<IndexSchema2xDocument>
 * @phpstan-type CategoryItem array{
 *      model?: array{
 *          categories?: array<array{id?: string}>,
 *          categoriesPath?: array<array{id?: string}>
 *      }
 *  }
 */
class DefaultSchema2xEventDocumentEnricher implements DocumentEnricher
{
    /**
     * @throws DocumentEnrichingException
     */
    public function enrichDocument(
        Resource $resource,
        IndexDocument $doc,
        string $processId,
    ): IndexDocument {

        /** @var array<string, mixed> $items */
        $items = $resource->data->getAssociativeArray('content.items');

        /** @var array{items?:array<string,mixed>} $main */
        $main = $this->findInList($items, 'type', 'main');

        /** @var CategoryItem$venue */
        $venue = $this->findInList($main['items'] ?? [], 'id', 'eventsCalendar-venue');
        $doc = $this->addCategories($doc, $venue);

        /** @var CategoryItem $ticketAgency */
        $ticketAgency = $this->findInList($main['items'] ?? [], 'id', 'eventsCalendar-ticketAgency');
        $doc = $this->addCategories($doc, $ticketAgency);

        /** @var CategoryItem $organizer */
        $organizer = $this->findInList($main['items'] ?? [], 'id', 'eventsCalendar-organizer');
        return $this->addCategories($doc, $organizer);
    }

    /**
     * @template E of IndexSchema2xDocument
     * @param E $doc
     * @param CategoryItem $item
     * @return E
     */
    private function addCategories(IndexDocument $doc, array $item): IndexDocument
    {
        foreach ($item['model']['categories'] ?? [] as $category) {
            if (isset($category['id'])) {
                $doc->sp_category[] = (string) $category['id'];
            }
        }
        foreach ($item['model']['categoriesPath'] ?? [] as $categoryPath) {
            if (isset($categoryPath['id'])) {
                $doc->sp_category_path[] = (string) $categoryPath['id'];
            }
        }

        return $doc;
    }

    /**
     * @param array<string, mixed|string> $list
     * @return array<string, mixed>
     */
    private function findInList(array $list, string $name, string $value): array
    {
        foreach ($list as $item) {
            /** @var array<string, mixed> $item */
            if ($item[$name] === $value) {
                return $item;
            }
        }

        return [];
    }

    public function cleanup(): void {}
}
