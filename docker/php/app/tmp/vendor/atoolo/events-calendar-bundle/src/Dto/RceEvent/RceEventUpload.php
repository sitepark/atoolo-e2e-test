<?php

declare(strict_types=1);

namespace Atoolo\EventsCalendar\Dto\RceEvent;

/**
 * @codeCoverageIgnore
 */
class RceEventUpload
{
    public function __construct(
        public readonly string $name,
        public readonly string $url,
        public readonly string $copyright,
    ) {}
}
