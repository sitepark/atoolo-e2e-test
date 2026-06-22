<?php

declare(strict_types=1);

namespace Atoolo\Seo\Dto\Sitemap;

use DateTime;

/**
 * https://www.sitemaps.org/protocol.html
 * @codeCoverageIgnore
 */
class IndexEntry
{
    public function __construct(
        public readonly string $loc,
        public readonly ?DateTime $lastMod = null,
    ) {}
}
