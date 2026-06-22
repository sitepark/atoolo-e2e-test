<?php

declare(strict_types=1);

namespace Atoolo\Microsite\Service;

use Atoolo\Microsite\Environment\MicrositeContext;
use Atoolo\Resource\Exception\InvalidResourceException;
use Atoolo\Resource\Exception\ResourceNotFoundException;
use Atoolo\Resource\ResourceLoader;
use Atoolo\Resource\ResourceLocation;
use Atoolo\Rewrite\Dto\Url;
use Psr\Log\LoggerInterface;

class MountService
{
    public function __construct(
        private readonly MicrositeContext $micrositeContext,
        private readonly ResourceLoader $resourceLoader,
        private readonly Platform $platform,
        private readonly LoggerInterface $logger,
    ) {}

    public function toMountedUrl(Url $url): Url
    {
        return $url;
        /*
        $params = $url->params ?? [];
        unset($params['p']);
        return $url->toBuilder()->params($params)->build();
        */
    }

    public function isMountable(string $path): bool
    {
        if ($this->micrositeContext->isMicrositePath($path)) {
            return false;
        }

        if ($this->isCurrentPathAlreadyMounted()) {
            return false;
        }

        if ($this->hasResourceMountableObjectType($path)) {
            return true;
        }

        return $this->isResourceInMicrositeNavigation($path);
    }

    private function isCurrentPathAlreadyMounted(): bool
    {
        if ($this->micrositeContext->currentPath === null) {
            return false;
        }

        if ($this->platform->fileExists($this->micrositeContext->resourceDir . $this->micrositeContext->currentPath)) {
            return true;
        }

        return false;
    }

    private function hasResourceMountableObjectType(string $path): bool
    {
        try {
            $location = ResourceLocation::ofPath($path);
            $resource = $this->resourceLoader->load($location);
            $mountableObjectTypes = $this->micrositeContext->mountableObjectTypes;
            return in_array($resource->objectType, $mountableObjectTypes);
        } catch (ResourceNotFoundException|InvalidResourceException $e) {
            $this->logger->warning(
                "Unable to determine if resource is mountable",
                ['path' => $path, 'exception' => $e],
            );
            return false;
        }
    }

    private function isResourceInMicrositeNavigation(string $path): bool
    {
        try {
            $location = ResourceLocation::ofPath($path);
            $resource = $this->resourceLoader->load($location);
            /** @var  array<array{siteGroup?:array{id: int}}> $navigationParents */
            $navigationParents = $resource->data->getAssociativeArray('base.trees.navigation.parents');
            foreach ($navigationParents as $parent) {
                if (isset($parent['siteGroup']['id']) && $parent['siteGroup']['id'] === $this->micrositeContext->siteId) {
                    return true;
                }
            }

            return false;

        } catch (ResourceNotFoundException|InvalidResourceException $e) {
            $this->logger->warning(
                "Unable to determine if resource is in microsite navigation",
                ['path' => $path, 'exception' => $e],
            );
            return false;
        }
    }
}
