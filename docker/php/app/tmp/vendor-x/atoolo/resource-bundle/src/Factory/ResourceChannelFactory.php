<?php

declare(strict_types=1);

namespace Atoolo\Resource\Factory;

use Atoolo\Resource\ResourceChannel;

interface ResourceChannelFactory
{
    public function create(): ResourceChannel;
}
