<?php

declare(strict_types=1);

namespace Atoolo\Seo\Dto\Sitemap;

use DateTime;

/**
 * https://www.sitemaps.org/protocol.html
 * @codeCoverageIgnore
 */
class UrlEntry
{
    /**
     * @param array<Link> $links https://developers.google.com/search/docs/specialty/international/localized-versions?hl=de#sitemap
     */
    public function __construct(
        public readonly string $loc,
        public readonly ?DateTime $lastMod,
        public readonly ChangeFreq $changeFreq,
        public readonly float $priority,
        public readonly array $links,
    ) {}
}
