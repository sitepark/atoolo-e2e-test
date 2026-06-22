<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Dto;

use Atoolo\Security\UserProfile;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @codeCoverageIgnore
 */
class User implements UserInterface, UserProfile
{
    /**
     * @param array<string> $roles
     * @param non-empty-string $id
     * @param non-empty-string $username
     */
    public function __construct(
        private readonly string $id,
        private readonly string $username,
        private readonly ?string $firstName,
        private readonly string $lastName,
        private readonly ?string $email,
        private readonly array $roles,
    ) {}

    public function getId(): string
    {
        return $this->id;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    public function eraseCredentials(): void {}

    public function getUserIdentifier(): string
    {
        return $this->username;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }
}
