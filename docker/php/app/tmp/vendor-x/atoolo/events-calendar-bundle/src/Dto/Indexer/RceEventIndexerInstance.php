<?php

declare(strict_types=1);

namespace Atoolo\EventsCalendar\Dto\Indexer;

/**
 * @codeCoverageIgnore
 */
class RceEventIndexerInstance
{
    /**
     * @param array<int> $groupPath
     */
    public function __construct(
        public readonly int $id,
        public readonly string $detailPageUrl,
        public readonly int $group,
        public readonly array $groupPath,
        public readonly ?string $kickerCategoryResourceLocation = null,
    ) {}
}
