<?php

declare(strict_types=1);

namespace Atoolo\Security\Test\SiteKit;

use Atoolo\Security\SiteKit\ProtectedPathMatcher;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\AccessMapInterface;

#[CoversClass(ProtectedPathMatcher::class)]
class ProtectedPathMatcherTest extends TestCase
{
    public function testMatchesNoRules(): void
    {
        $accessMap = $this->createStub(AccessMapInterface::class);
        $accessMap->method('getPatterns')->willReturn([null]);
        $matcher = new ProtectedPathMatcher($accessMap);
        $this->assertFalse(
            $matcher->matches($this->createStub(Request::class)),
            'No rules should match',
        );
    }
    public function testMatchesEmptyRules(): void
    {
        $accessMap = $this->createStub(AccessMapInterface::class);
        $accessMap->method('getPatterns')->willReturn([[]]);
        $matcher = new ProtectedPathMatcher($accessMap);
        $this->assertFalse(
            $matcher->matches($this->createStub(Request::class)),
            'No rules should match',
        );
    }

    public function testMatches(): void
    {
        $accessMap = $this->createStub(AccessMapInterface::class);
        $accessMap->method('getPatterns')->willReturn([['test']]);
        $matcher = new ProtectedPathMatcher($accessMap);
        $this->assertTrue(
            $matcher->matches($this->createStub(Request::class)),
            'Rules should match',
        );
    }
}
