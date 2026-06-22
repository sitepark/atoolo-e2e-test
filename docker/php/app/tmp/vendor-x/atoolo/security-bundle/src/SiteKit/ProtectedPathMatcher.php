<?php

declare(strict_types=1);

namespace Atoolo\Security\SiteKit;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\Security\Http\AccessMapInterface;

class ProtectedPathMatcher implements RequestMatcherInterface
{
    /**
     * @var AccessMapInterface
     */
    private $accessMap;

    public function __construct(AccessMapInterface $accessMap)
    {
        $this->accessMap = $accessMap;
    }

    public function matches(Request $request): bool
    {
        $roles = $this->accessMap->getPatterns($request)[0];
        return $roles !== null && count($roles) > 0;
    }
}
