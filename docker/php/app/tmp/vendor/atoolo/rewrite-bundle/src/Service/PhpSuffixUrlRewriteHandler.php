<?php

declare(strict_types=1);

namespace Atoolo\Rewrite\Service;

use Atoolo\Rewrite\Dto\Url;
use Atoolo\Rewrite\Dto\UrlRewriterHandlerContext;
use Atoolo\Rewrite\Dto\UrlRewriteType;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;

#[AsAlias(id: 'atoolo_rewrite.url_rewrite_handler.php_suffix')]
class PhpSuffixUrlRewriteHandler implements UrlRewriterHandler
{
    public function rewrite(
        Url $url,
        UrlRewriterHandlerContext $context,
    ): Url {

        // rewrite only internal urls
        if ($context->origin->isFullyQualified()) {
            return $url;
        }

        // rewrite only internal resources
        if ($context->type !== UrlRewriteType::LINK) {
            return $url;
        }

        if ($url->path === null) {
            return $url;
        }

        $isWebIesPath = str_starts_with($url->path, '/WEB-IES/');
        if ($isWebIesPath) {
            return $url;
        }

        if (!str_ends_with($url->path, '.php')) {
            return $url;
        }

        $builder = $url->toBuilder();
        if (str_ends_with($url->path, '/index.php')) {
            $builder->path(substr($url->path, 0, -9));
        } else {
            $builder->path(substr($url->path, 0, -4));
        }

        return $builder->build();
    }
}
