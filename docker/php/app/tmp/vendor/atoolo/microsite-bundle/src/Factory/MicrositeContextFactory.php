<?php

declare(strict_types=1);

namespace Atoolo\Microsite\Factory;

use Atoolo\Microsite\Environment\MicrositeContext;
use Atoolo\Resource\ResourceChannel;
use Atoolo\Resource\ResourceHierarchyLoader;
use Atoolo\Resource\ResourceLocation;
use Atoolo\Rewrite\Service\UrlRewriteContext;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;

class MicrositeContextFactory
{
    /**
     * @param array<string> $mountableObjectTypes
     */
    public function __construct(
        private readonly RequestStack $requestStack,
        #[Autowire(service: 'atoolo_resource.resource_channel')]
        private readonly ResourceChannel $resourceChannel,
        private readonly UrlRewriteContext $rewriteContext,
        #[Autowire(service: 'atoolo_resource.navigation_hierarchy_loader')]
        private readonly ResourceHierarchyLoader $navigationHierarchyLoader,
        #[Autowire(param: 'atoolo_microsite.mountable_object_types')]
        private readonly array $mountableObjectTypes,
    ) {}

    public function create(): ?MicrositeContext
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request) {
            return null;
        }
        $micrositeHost = $request->server->getString('ATOOLO_MICROSITE_HOST');
        $micrositePath = $request->server->getString('ATOOLO_MICROSITE_PATH');
        $mainHost = $request->server->getString('ATOOLO_MAIN_HOST');

        if (empty($micrositeHost) || empty($micrositePath) || empty($mainHost)) {
            return null;
        }

        return new MicrositeContext(
            resourceDir: $this->resourceChannel->resourceDir,
            currentPath: $this->rewriteContext->getBasePath(),
            micrositeHost: $micrositeHost,
            micrositePath: $micrositePath,
            mainHost: $mainHost,
            siteId: $this->getSiteId($micrositePath),
            mountableObjectTypes: $this->mountableObjectTypes ?? [],
        );
    }

    private function getSiteId(string $homeResourcePath): int
    {
        $home = $this->navigationHierarchyLoader->loadRoot(ResourceLocation::ofPath($homeResourcePath . '/'));
        return $home->data->getInt('siteGroup.id');
    }
}
