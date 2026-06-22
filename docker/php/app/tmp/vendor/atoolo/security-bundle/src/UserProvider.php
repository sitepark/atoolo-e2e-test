<?php

declare(strict_types=1);

namespace Atoolo\Security;

use Atoolo\Security\Entity\User;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

/**
 * @implements UserProviderInterface<UserInterface>
 */
class UserProvider implements UserProviderInterface
{
    /**
     * @var UserLoader
     */
    private $userLoader;

    /**
     * @var array<UserInterface>
     */
    private $users;

    public function __construct(UserLoader $userLoader)
    {
        $this->userLoader = $userLoader;
    }

    /**
     * The loadUserByIdentifier() method was introduced in Symfony 5.3.
     * In previous versions it was called loadUserByUsername()
     *
     * Symfony calls this method if you use features like switch_user
     * or remember_me. If you're not using these features, you do not
     * need to implement this method.
     *
     * @throws UserNotFoundException if the user is not found
     */
    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        // Load a User object from your data source or throw
        // UserNotFoundException. The $identifier argument is whatever
        // value is being returned by the getUserIdentifier() method
        // in your User class.

        if ($this->users === null) {
            $this->users = $this->userLoader->load();
        }

        if (!isset($this->users[$identifier])) {
            throw new UserNotFoundException($identifier);
        }

        return $this->users[$identifier];
    }

    /**
     * Refreshes the user after being reloaded from the session.
     *
     * When a user is logged in, at the beginning of each request, the
     * User object is loaded from the session and then this method is
     * called. Your job is to make sure the user's data is still fresh by,
     * for example, re-querying for fresh User data.
     *
     * If your firewall is "stateless: true" (for a pure API), this
     * method is not called.
     *
     */
    public function refreshUser(UserInterface $user): UserInterface
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(
                sprintf('Invalid user class "%s".', get_class($user)),
            );
        }
        return $this->loadUserByIdentifier($user->getUserIdentifier());
    }

    /**
     * Tells Symfony to use this provider for this User class.
     */
    public function supportsClass(string $class): bool
    {
        return User::class === $class || is_subclass_of($class, User::class);
    }
}
