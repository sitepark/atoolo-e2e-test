<?php

declare(strict_types=1);

namespace Atoolo\Rewrite\Service;

use Atoolo\Resource\ResourceLocation;
use Atoolo\Resource\Service\PParameterService;
use Atoolo\Rewrite\Dto\Url;
use Atoolo\Rewrite\Dto\UrlRewriterHandlerContext;
use Atoolo\Rewrite\Dto\UrlRewriteType;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsAlias(id: 'atoolo_rewrite.url_rewrite_handler.same_navigation')]
class SameNavigationUrlRewriteHandler implements UrlRewriterHandler
{
    public function __construct(
        private readonly UrlRewriteContext $urlRewriteContext,
        #[Autowire(service: 'atoolo_resource.p_parameter_service')]
        private readonly PParameterService $pParameterService,
    ) {}

    public function rewrite(
        Url $url,
        UrlRewriterHandlerContext $context,
    ): Url {
        if ($context->type !== UrlRewriteType::LINK) {
            return $url;
        }
        if ($this->urlRewriteContext->isSameNavigation() === false) {
            return $url;
        }
        if ($this->urlRewriteContext->getResourceLocation() === null) {
            return $url;
        }
        if ($url->host !== null) {
            return $url;
        }
        if (empty($url->path)) {
            return $url;
        }

        $pParameter = $this->pParameterService->getPParameterForForeignParent(
            ResourceLocation::of($this->urlRewriteContext->getResourceLocation()),
            ResourceLocation::ofPath($context->origin->path ?? '/'),
        );

        return $url->toBuilder()->param('p', $pParameter)->build();
    }
}
