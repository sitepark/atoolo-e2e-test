<?php

declare(strict_types=1);

namespace Atoolo\Security\Test;

use Atoolo\Security\Entity\User;
use Atoolo\Security\UserLoader;
use Atoolo\Security\UserProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

#[CoversClass(UserProvider::class)]
class UserProviderTest extends TestCase
{
    public function testLoadUserByIdentifier(): void
    {
        $user = $this->createStub(UserInterface::class);
        $userLoader = $this->createStub(UserLoader::class);
        $userLoader->method('load')->willReturn(['test' => $user]);
        $userProvider = new UserProvider($userLoader);

        $user = $userProvider->loadUserByIdentifier('test');

        $this->assertEquals(
            $user,
            $userProvider->loadUserByIdentifier('test'),
            'User should be loaded',
        );
    }

    public function testLoadUserByIdentifierNotFound(): void
    {
        $userLoader = $this->createStub(UserLoader::class);
        $userProvider = new UserProvider($userLoader);

        $this->expectException(UserNotFoundException::class);
        $userProvider->loadUserByIdentifier('test');
    }

    public function testRefreshUser(): void
    {
        $user = new User('test', []);
        $userLoader = $this->createStub(UserLoader::class);
        $userLoader->method('load')->willReturn(['test' => $user]);
        $userProvider = new UserProvider($userLoader);

        $userFromSession = new User('test', []);

        $this->assertSame(
            $user,
            $userProvider->refreshUser($userFromSession),
            'User should be refreshed',
        );
    }

    public function testRefreshUserWithInvalidInstance(): void
    {
        $userLoader = $this->createStub(UserLoader::class);
        $userProvider = new UserProvider($userLoader);

        $this->expectException(UnsupportedUserException::class);
        $userProvider->refreshUser($this->createStub(UserInterface::class));
    }

    public function testSupportsClass(): void
    {
        $userLoader = $this->createStub(UserLoader::class);
        $userProvider = new UserProvider($userLoader);
        $this->assertTrue(
            $userProvider->supportsClass(User::class),
            'User class should be supported',
        );
    }

    public function testSupportsClassWithUnsupportedClass(): void
    {
        $userLoader = $this->createStub(UserLoader::class);
        $userProvider = new UserProvider($userLoader);
        $this->assertFalse(
            $userProvider->supportsClass(UserInterface::class),
            'User class should be supported',
        );
    }
}
