<?php

declare(strict_types=1);

namespace Atoolo\Security\Entity;

use Atoolo\Security\Exception\SecurityException;
use Atoolo\Security\SiteKit\RoleMapper;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

final class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @var non-empty-string
     */
    private string $username;

    private ?string $password = null;

    /**
     * @var callable(): string
     */
    public $passwordCallback;

    /**
     * @var string[]
     */
    private array $roles;

    /**
     * @param non-empty-string $username
     * @param string[] $roles
     */
    public function __construct(string $username, array $roles)
    {
        $this->username = $username;
        $this->roles    = $roles;
    }

    /**
     * @param array{
     *     username?: non-empty-string,
     *     password?: string,
     *     roles?: array<string>
     * } $data
     * @return User
     */
    public static function ofArray(array $data): User
    {

        if (!isset($data['username'], $data['password'], $data['roles'])) {
            throw new SecurityException(
                'Invalid User data provided. Expected array with keys ' .
                'username, password and roles',
            );
        }

        $roles = RoleMapper::map($data['roles']);
        $user  = new User($data['username'], $roles);
        $user->setPassword($data['password']);

        return $user;
    }

    /**
     * @return array<string>
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    /**
     * @param callable(): string $passwordCallback
     */
    public function setPasswordCallback(callable $passwordCallback): void
    {
        $this->passwordCallback = $passwordCallback;
    }

    public function getPassword(): ?string
    {
        if ($this->passwordCallback !== null) {
            return ($this->passwordCallback)();
        }
        return $this->password;
    }

    public function eraseCredentials(): void {}

    /**
     * The public representation of the user (e.g. a username,
     * an email address, etc.)
     *
     * @return non-empty-string
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return $this->username;
    }
}
