<?php

declare(strict_types=1);

namespace Atoolo\EventsCalendar\Dto\RceEvent;

use DateTime;

/**
 * @codeCoverageIgnore
 */
class RceEventDate
{
    public function __construct(
        public readonly string $hashId,
        public readonly DateTime $startDate,
        public readonly DateTime $endDate,
        public readonly bool $blacklisted,
        public readonly bool $soldOut,
        public readonly bool $cancelled,
        public readonly bool $postponed,
    ) {}
}
