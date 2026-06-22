<?php

declare(strict_types=1);

namespace Atoolo\Seo\Dto\Sitemap;

/**
 * @codeCoverageIgnore
 */
class Link
{
    public function __construct(
        public readonly string $rel,
        public readonly string $hrefLang,
        public readonly string $href,
    ) {}
}
