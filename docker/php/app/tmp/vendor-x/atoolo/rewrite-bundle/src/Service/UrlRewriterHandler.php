<?php

declare(strict_types=1);

namespace Atoolo\Rewrite\Service;

use Atoolo\Rewrite\Dto\Url;
use Atoolo\Rewrite\Dto\UrlRewriterHandlerContext;

interface UrlRewriterHandler
{
    public function rewrite(
        Url $url,
        UrlRewriterHandlerContext $context,
    ): Url;
}
