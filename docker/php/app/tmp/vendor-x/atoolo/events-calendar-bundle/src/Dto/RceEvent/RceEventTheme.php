<?php

declare(strict_types=1);

namespace Atoolo\EventsCalendar\Dto\RceEvent;

/**
 * @codeCoverageIgnore
 */
class RceEventTheme
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
    ) {}
}
