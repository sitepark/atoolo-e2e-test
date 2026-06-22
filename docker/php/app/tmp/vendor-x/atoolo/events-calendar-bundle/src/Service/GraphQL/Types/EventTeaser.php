<?php

declare(strict_types=1);

namespace Atoolo\EventsCalendar\Service\GraphQL\Types;

use Atoolo\GraphQL\Search\Types\Link;
use Atoolo\Resource\Resource;
use Atoolo\GraphQL\Search\Types\Teaser;

/**
 * @codeCoverageIgnore
 */
class EventTeaser extends Teaser
{
    public function __construct(
        ?Link $link,
        public readonly ?string $headline,
        public readonly ?string $text,
        public readonly Resource $resource,
    ) {
        parent::__construct($link);
    }
}
