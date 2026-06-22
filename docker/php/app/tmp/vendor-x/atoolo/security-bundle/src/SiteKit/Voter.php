<?php

declare(strict_types=1);

namespace Atoolo\Security\SiteKit;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter as VoterBase;
use Symfony\Component\Security\Http\AccessMapInterface;

/**
 * @extends VoterBase<string,Request>
 */
class Voter extends VoterBase
{
    public const SITEKIT_PUBLICATION = 'SITEKIT_PUBLICATION';

    private AccessMapInterface $accessMap;

    public function __construct(AccessMapInterface $accessMap)
    {
        $this->accessMap = $accessMap;
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return $subject instanceof Request && $attribute === self::SITEKIT_PUBLICATION;
    }

    protected function voteOnAttribute(
        string $attribute,
        mixed $subject,
        TokenInterface $token,
    ): bool {
        $patterns = $this->accessMap->getPatterns($subject);

        $roles = $patterns[0] ?? null;

        if ($roles === null || count($roles) === 0) {
            return true;
        }

        $roleMatches = array_intersect($roles, $token->getRoleNames());

        return count($roleMatches) > 0;
    }
}
