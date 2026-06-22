<?php

declare(strict_types=1);

namespace Atoolo\EventsCalendar\Dto\RceEvent;

/**
 * @codeCoverageIgnore
 */
class RceEventAddress
{
    public function __construct(
        public readonly string $name,
        public readonly string $gemkey,
        public readonly string $street,
        public readonly string $zip,
        public readonly string $city,
    ) {}
}
