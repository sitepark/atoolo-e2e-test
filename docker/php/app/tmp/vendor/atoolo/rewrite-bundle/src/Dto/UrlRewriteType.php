<?php

declare(strict_types=1);

namespace Atoolo\Rewrite\Dto;

/**
 * @codeCoverageIgnore
 */
enum UrlRewriteType
{
    case IMAGE;
    case MEDIA;
    case LINK;
}
