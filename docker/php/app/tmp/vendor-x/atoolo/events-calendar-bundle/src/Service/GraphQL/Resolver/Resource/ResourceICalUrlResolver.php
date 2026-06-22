<?php

declare(strict_types=1);

namespace Atoolo\EventsCalendar\Service\GraphQL\Resolver\Resource;

use Atoolo\Resource\Resource;

class ResourceICalUrlResolver
{
    public function getICalUrl(
        Resource $resource,
    ): ?string {
        $isExternal = str_starts_with($resource->location, 'http://')
            || str_starts_with($resource->location, 'https://');
        $langCode = !empty($resource->lang->code)
            ? '/' . $resource->lang->code
            : '';
        return $isExternal
            ? null
            : '/api/ical/resource' . $langCode . $resource->location;
    }
}
