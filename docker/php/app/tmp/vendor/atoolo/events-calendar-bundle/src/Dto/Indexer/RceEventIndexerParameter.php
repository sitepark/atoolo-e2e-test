<?php

declare(strict_types=1);

namespace Atoolo\EventsCalendar\Dto\Indexer;

/**
 * @codeCoverageIgnore
 */
class RceEventIndexerParameter
{
    /**
     * @param array<RceEventIndexerInstance> $instanceList
     * @param array<string> $categoryRootResourceLocations
     * @param array<string,array<int,int>> $simpleCategoryMap
     */
    public function __construct(
        public readonly string $source,
        public readonly array $instanceList,
        public readonly array $categoryRootResourceLocations,
        public readonly array $simpleCategoryMap,
        public readonly int $cleanupThreshold,
        public readonly string $exportUrl,
    ) {}
}
