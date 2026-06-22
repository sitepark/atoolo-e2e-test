<?php

declare(strict_types=1);

namespace Atoolo\EventsCalendar\Dto\RceEvent;

/**
 * @codeCoverageIgnore
 */
class RceEventSource
{
    public function __construct(
        public readonly string $userId,
        public readonly string $supply,
    ) {}
}
