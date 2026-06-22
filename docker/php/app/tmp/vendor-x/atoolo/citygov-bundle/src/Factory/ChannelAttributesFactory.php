<?php

declare(strict_types=1);

namespace Atoolo\CityGov\Factory;

use Atoolo\CityGov\ChannelAttributes;
use Atoolo\Resource\ResourceChannel;

class ChannelAttributesFactory
{
    private const SP_VV_ALTERNATIVE_TITLE = 'sp_vv_alternativeTitle';

    public function __construct(
        private readonly ResourceChannel $resourceChannel,
    ) {}

    public function create(): ChannelAttributes
    {
        $addTitleBool = $this->resourceChannel->attributes->getBool(self::SP_VV_ALTERNATIVE_TITLE);
        $addTitleString = strtolower(
            $this->resourceChannel->attributes->getString(self::SP_VV_ALTERNATIVE_TITLE, 'false'),
        );
        return new ChannelAttributes($addTitleBool || $addTitleString === 'true');
    }
}
