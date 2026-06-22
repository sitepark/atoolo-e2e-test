<?php

declare(strict_types=1);

namespace Atoolo\EventsCalendar\Service\GraphQL\Resolver\Teaser;

use Atoolo\EventsCalendar\Dto\Scheduling\Scheduling;
use Atoolo\EventsCalendar\Service\GraphQL\Resolver\Resource\ResourceICalUrlResolver;
use Atoolo\EventsCalendar\Service\GraphQL\Resolver\Resource\ResourceSchedulingResolver;
use Atoolo\EventsCalendar\Service\GraphQL\Types\EventTeaser;
use Atoolo\GraphQL\Search\Resolver\Resolver;
use Atoolo\GraphQL\Search\Resolver\Resource\ResourceAssetResolver;
use Atoolo\GraphQL\Search\Resolver\Resource\ResourceKickerResolver;
use Atoolo\GraphQL\Search\Resolver\Resource\ResourceSymbolicAssetResolver;
use Atoolo\GraphQL\Search\Types\Asset;
use Overblog\GraphQLBundle\Definition\ArgumentInterface;

class EventTeaserResolver implements Resolver
{
    public function __construct(
        private readonly ResourceAssetResolver $assetResolver,
        private readonly ResourceSymbolicAssetResolver $symbolicAssetResolver,
        private readonly ResourceKickerResolver $kickerResolver,
        private readonly ResourceSchedulingResolver $schedulingResolver,
        private readonly ResourceICalUrlResolver $iCalUrlResolver,
    ) {}

    public function getUrl(
        EventTeaser $teaser,
    ): ?string {
        return $teaser->link?->url;
    }

    public function getKicker(
        EventTeaser $teaser,
    ): ?string {
        return $this->kickerResolver->getKicker($teaser->resource);
    }

    public function getAsset(
        EventTeaser $teaser,
        ArgumentInterface $args,
    ): ?Asset {
        return $this->assetResolver->getAsset($teaser->resource, $args);
    }

    public function getSymbolicAsset(
        EventTeaser $teaser,
        ArgumentInterface $args,
    ): ?Asset {
        return $this->symbolicAssetResolver
            ->getSymbolicAsset($teaser->resource, $args);
    }

    /**
     * @return Scheduling[]
     */
    public function getSchedulings(
        EventTeaser $teaser,
        ArgumentInterface $args,
    ): array {
        return $this->schedulingResolver
            ->getSchedulings($teaser->resource);
    }

    public function getICalUrl(
        EventTeaser $teaser,
        ArgumentInterface $args,
    ): ?string {
        return $this->iCalUrlResolver
            ->getICalUrl($teaser->resource);
    }
}
