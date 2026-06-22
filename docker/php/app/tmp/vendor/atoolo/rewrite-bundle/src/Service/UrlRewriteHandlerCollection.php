<?php

declare(strict_types=1);

namespace Atoolo\Rewrite\Service;

use Atoolo\Rewrite\Dto\Url;
use Atoolo\Rewrite\Dto\UrlRewriteOptions;
use Atoolo\Rewrite\Dto\UrlRewriterHandlerContext;
use Atoolo\Rewrite\Dto\UrlRewriteType;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

#[AsAlias(id: 'atoolo_rewrite.url_rewriter')]
class UrlRewriteHandlerCollection implements UrlRewriter
{
    /**
     * @param iterable<UrlRewriterHandler> $handlers
     */
    public function __construct(
        #[AutowireIterator('atoolo_rewrite.url_rewrite_handler')]
        private readonly iterable $handlers,
    ) {}

    public function rewrite(UrlRewriteType $type, string $origin, UrlRewriteOptions $options): string
    {
        $url = Url::builder()->parse($origin)->build();

        $context = new UrlRewriterHandlerContext(
            origin: $url,
            type: $type,
            options: $options,
        );
        foreach ($this->handlers as $handler) {
            $url = $handler->rewrite($url, $context);
        }

        return $url->__toString();
    }
}
