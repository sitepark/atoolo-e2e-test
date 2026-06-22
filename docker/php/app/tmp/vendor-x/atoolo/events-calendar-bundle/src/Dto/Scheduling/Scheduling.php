<?php

declare(strict_types=1);

namespace Atoolo\EventsCalendar\Dto\Scheduling;

use DateTime;

/**
 * @codeCoverageIgnore
 */
class Scheduling
{
    public function __construct(
        public readonly DateTime $start,
        public readonly ?DateTime $end,
        public readonly bool $isFullDay,
        public readonly bool $hasStartTime,
        public readonly bool $hasEndTime,
        public readonly ?string $rRule,
    ) {}
}
