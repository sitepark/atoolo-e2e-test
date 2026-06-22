<?php

declare(strict_types=1);

namespace Atoolo\Rewrite\Dto;

/**
 * @codeCoverageIgnore
 */
class UrlRewriterHandlerContext
{
    public function __construct(
        public readonly Url $origin,
        public readonly UrlRewriteType $type,
        public readonly UrlRewriteOptions $options,
    ) {}
}
