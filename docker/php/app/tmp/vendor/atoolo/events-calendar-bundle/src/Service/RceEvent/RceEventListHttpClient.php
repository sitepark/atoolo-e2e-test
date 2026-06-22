<?php

declare(strict_types=1);

namespace Atoolo\EventsCalendar\Service\RceEvent;

/**
 * @codeCoverageIgnore
 */
class RceEventListHttpClient
{
    public function get(string $url): string
    {
        $content = file_get_contents($url);
        if ($content === false) {
            throw new \RuntimeException('Unable to get content from ' . $url);
        }
        return $content;
    }
}
