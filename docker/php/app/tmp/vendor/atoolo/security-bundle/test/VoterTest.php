<?php

declare(strict_types=1);

namespace Atoolo\Security\Test\SiteKit;

use Atoolo\Security\SiteKit\Voter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;
use Symfony\Component\Security\Http\AccessMapInterface;

#[CoversClass(Voter::class)]
class VoterTest extends TestCase
{
    public function testWithInvalidSubjectVote(): void
    {
        $accessMap = $this->createStub(AccessMapInterface::class);
        $voter = new Voter($accessMap);

        $token = $this->createStub(TokenInterface::class);

        $this->assertEquals(
            VoterInterface::ACCESS_ABSTAIN,
            $voter->vote($token, 'invalid', [Voter::SITEKIT_PUBLICATION]),
            'Vote should fail',
        );
    }

    public function testVoteWithoutPattern(): void
    {
        $accessMap = $this->createStub(AccessMapInterface::class);
        $accessMap->method('getPatterns')->willReturn([]);
        $voter = new Voter($accessMap);

        $token = $this->createStub(TokenInterface::class);
        $subject = $this->createStub(Request::class);

        $this->assertEquals(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($token, $subject, [Voter::SITEKIT_PUBLICATION]),
            'Vote should succeed',
        );
    }

    public function testVoteWithoutRoles(): void
    {
        $accessMap = $this->createStub(AccessMapInterface::class);
        $accessMap->method('getPatterns')->willReturn([[]]);
        $voter = new Voter($accessMap);

        $token = $this->createStub(TokenInterface::class);
        $subject = $this->createStub(Request::class);

        $this->assertEquals(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($token, $subject, [Voter::SITEKIT_PUBLICATION]),
            'Vote should succeed',
        );
    }

    public function testVoteWithUnmatchedRoles(): void
    {
        $accessMap = $this->createStub(AccessMapInterface::class);
        $accessMap->method('getPatterns')->willReturn([['ROLE_ADMIN']]);
        $voter = new Voter($accessMap);

        $token = $this->createStub(TokenInterface::class);
        $subject = $this->createStub(Request::class);

        $this->assertEquals(
            VoterInterface::ACCESS_DENIED,
            $voter->vote($token, $subject, [Voter::SITEKIT_PUBLICATION]),
            'Vote should fail',
        );
    }

    public function testVoteWithMatchedRoles(): void
    {
        $accessMap = $this->createStub(AccessMapInterface::class);
        $accessMap->method('getPatterns')->willReturn([['ROLE_ADMIN']]);
        $voter = new Voter($accessMap);

        $token = $this->createStub(TokenInterface::class);
        $token->method('getRoleNames')->willReturn(['ROLE_ADMIN']);
        $subject = $this->createStub(Request::class);

        $this->assertEquals(
            VoterInterface::ACCESS_GRANTED,
            $voter->vote($token, $subject, [Voter::SITEKIT_PUBLICATION]),
            'Vote should fail',
        );
    }
}
