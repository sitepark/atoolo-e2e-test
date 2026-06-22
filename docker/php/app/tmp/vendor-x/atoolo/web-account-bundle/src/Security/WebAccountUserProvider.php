<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Security;

use Atoolo\WebAccount\Dto\User;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @implements UserProviderInterface<User>
 */
#[AsAlias(id: 'atoolo_web_account.user_provider')]
class WebAccountUserProvider implements UserProviderInterface
{
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        throw new UserNotFoundException('Not supported directly.');
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException();
        }
        return $user;
    }

    public function supportsClass(string $class): bool
    {
        return is_a($class, User::class, true);
    }
}
