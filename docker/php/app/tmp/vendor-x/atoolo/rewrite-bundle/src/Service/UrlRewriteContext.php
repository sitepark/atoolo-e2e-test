<?php

declare(strict_types=1);

namespace Atoolo\Rewrite\Service;

use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsAlias(id: 'atoolo_rewrite.url_rewrite_context')]
class UrlRewriteContext
{
    private ?string $scheme = null;

    private ?string $host = null;

    private ?string $basePath = null;

    private ?string $resourceLocation = null;

    private bool $sameNavigation = false;

    public function __construct(RequestStack $requestStack)
    {
        $request = $requestStack->getCurrentRequest();
        if ($request !== null) {
            $this->scheme = $request->getScheme();
            $this->host = $request->getHost();
            $this->basePath = $request->getBasePath();
        }
    }

    public function setScheme(string $scheme): void
    {
        $this->scheme = $scheme;
    }

    public function setHost(string $host): void
    {
        $this->host = $host;
    }

    public function setBasePath(string $basePath): void
    {
        $this->basePath = $basePath;
    }

    public function setResourceLocation(string $resourceLocation): void
    {
        $this->resourceLocation = $resourceLocation;
    }

    public function getScheme(): ?string
    {
        return $this->scheme;
    }

    public function getHost(): ?string
    {
        return $this->host;
    }

    public function getBasePath(): ?string
    {
        return $this->basePath;
    }

    public function getResourceLocation(): ?string
    {
        return $this->resourceLocation;
    }

    public function setSameNavigation(bool $sameNavigation): void
    {
        $this->sameNavigation = $sameNavigation;
    }

    public function isSameNavigation(): bool
    {
        return $this->sameNavigation;
    }
}
