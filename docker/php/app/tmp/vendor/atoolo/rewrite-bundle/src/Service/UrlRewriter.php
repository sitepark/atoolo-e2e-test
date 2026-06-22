<?php

declare(strict_types=1);

namespace Atoolo\Rewrite\Service;

use Atoolo\Rewrite\Dto\UrlRewriteOptions;
use Atoolo\Rewrite\Dto\UrlRewriteType;

interface UrlRewriter
{
    public function rewrite(
        UrlRewriteType $type,
        string $origin,
        UrlRewriteOptions $options,
    ): string;
}
