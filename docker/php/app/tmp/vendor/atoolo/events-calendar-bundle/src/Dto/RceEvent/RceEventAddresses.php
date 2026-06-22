<?php

declare(strict_types=1);

namespace Atoolo\EventsCalendar\Dto\RceEvent;

/**
 * @codeCoverageIgnore
 */
class RceEventAddresses
{
    public function __construct(
        public readonly ?RceEventAddress $location = null,
        public readonly ?RceEventAddress $organizer = null,
    ) {}
}
