<?php

declare(strict_types=1);

namespace Atoolo\Resource;

/**
 * In the Atoolo context, resources are aggregated data from
 * IES (Sitepark's content management system).
 */
class Resource
{
    public function __construct(
        public readonly string $location,
        public readonly string $id,
        public readonly string $name,
        public readonly string $objectType,
        public readonly ResourceLanguage $lang,
        public readonly DataBag $data,
    ) {}

    public function toLocation(): ResourceLocation
    {
        return ResourceLocation::of($this->location, $this->lang);
    }
}
