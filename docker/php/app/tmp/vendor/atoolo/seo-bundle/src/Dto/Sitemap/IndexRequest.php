<?php

declare(strict_types=1);

namespace Atoolo\Seo\Dto\Sitemap;

/**
 * @codeCoverageIgnore
 */
class IndexRequest
{
    /**
     * @param array<string> $siteExcludes
     * @param array<string> $siteIncludes
     */
    public function __construct(
        public readonly array $siteExcludes,
        public readonly array $siteIncludes,
    ) {}
}
