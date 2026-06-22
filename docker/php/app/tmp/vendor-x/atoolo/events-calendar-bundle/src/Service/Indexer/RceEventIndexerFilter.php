<?php

declare(strict_types=1);

namespace Atoolo\EventsCalendar\Service\Indexer;

use Atoolo\EventsCalendar\Dto\RceEvent\RceEventDate;
use Atoolo\EventsCalendar\Dto\RceEvent\RceEventListItem;

interface RceEventIndexerFilter
{
    public function accept(
        RceEventListItem $event,
        RceEventDate $eventDate,
    ): bool;
}
