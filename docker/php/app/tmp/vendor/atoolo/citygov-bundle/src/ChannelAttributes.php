<?php

declare(strict_types=1);

namespace Atoolo\CityGov;

class ChannelAttributes
{
    public function __construct(
        public readonly bool $addAlternativeDocuments,
    ) {}
}
