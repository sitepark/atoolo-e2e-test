<?php

declare(strict_types=1);

namespace Atoolo\CityGov\Service\GraphQL\Types;

use Atoolo\GraphQL\Search\Types\TeaserFeature;

/**
 * @codeCoverageIgnore
 */
class OnlineServiceFeature extends TeaserFeature
{
    /**
     * @param OnlineService[] $onlineServices
     */
    public function __construct(
        ?string $label = null,
        public readonly array $onlineServices = [],
    ) {
        parent::__construct($label);
    }
}
