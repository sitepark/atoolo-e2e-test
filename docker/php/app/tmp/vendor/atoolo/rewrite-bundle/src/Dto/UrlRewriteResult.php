<?php

declare(strict_types=1);

namespace Atoolo\Rewrite\Dto;

/**
 * @codeCoverageIgnore
 */
class UrlRewriteResult
{
    public function __construct(
        public readonly Url $url,
        public readonly bool $last,
    ) {}
}
