<?php

declare(strict_types=1);

namespace Atoolo\EventsCalendar\Service\Indexer;

use Atoolo\EventsCalendar\Dto\RceEvent\RceEventDate;
use Atoolo\EventsCalendar\Dto\RceEvent\RceEventListItem;
use DateTime;

class RceEventIndexerDateFilter implements RceEventIndexerFilter
{
    private DateTime $date;
    public function __construct(
        ?DateTime $date = null,
    ) {
        if ($date === null) {
            $date = new DateTime();
            $date->setTime(0, 0);
        }
        $this->date = $date;
    }
    public function accept(
        RceEventListItem $event,
        RceEventDate $eventDate,
    ): bool {
        return
            $event->active
            && !$eventDate->blacklisted
            && $eventDate->startDate >= $this->date;
    }
}
