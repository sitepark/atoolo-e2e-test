<?php

declare(strict_types=1);

namespace Atoolo\CityGov\Service\GraphQL\Types;

use Atoolo\GraphQL\Search\Types\Link;

/**
 * @codeCoverageIgnore
 */
class OnlineService
{
    public function __construct(
        public readonly Link $link,
    ) {}
}
