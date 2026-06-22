<?php

declare(strict_types=1);

namespace Atoolo\Seo\Dto\Sitemap;

/**
 * @codeCoverageIgnore
 */
class EntriesRequest
{
    /**
     * @param array<string> $siteExcludes
     * @param array<string> $siteIncludes
     */
    public function __construct(
        public readonly int $page,
        public readonly array $siteExcludes,
        public readonly array $siteIncludes,
    ) {}
}
