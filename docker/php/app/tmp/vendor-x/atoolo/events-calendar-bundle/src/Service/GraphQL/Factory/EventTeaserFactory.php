<?php

declare(strict_types=1);

namespace Atoolo\EventsCalendar\Service\GraphQL\Factory;

use Atoolo\EventsCalendar\Service\GraphQL\Types\EventTeaser;
use Atoolo\GraphQL\Search\Factory\TeaserFactory;
use Atoolo\GraphQL\Search\Factory\LinkFactory;
use Atoolo\GraphQL\Search\Types\Teaser;
use Atoolo\Resource\Resource;

class EventTeaserFactory implements TeaserFactory
{
    public function __construct(
        private readonly LinkFactory $linkFactory,
    ) {}

    public function create(Resource $resource): Teaser
    {
        $link = $this->linkFactory->create(
            $resource,
        );

        $headline = $resource->data->getString(
            'base.teaser.headline',
            $resource->name,
        );
        $text = $resource->data->getString('base.teaser.text');

        return new EventTeaser(
            $link,
            $headline,
            $text === '' ? null : $text,
            $resource,
        );
    }
}
