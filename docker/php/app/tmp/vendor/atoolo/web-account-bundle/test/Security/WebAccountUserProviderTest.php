<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Test\Security;

use Atoolo\WebAccount\Dto\User;
use Atoolo\WebAccount\Security\WebAccountUserProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

#[CoversClass(WebAccountUserProvider::class)]
class WebAccountUserProviderTest extends TestCase
{
    public function testLoadUserByIdentifier(): void
    {
        $this->expectException(UserNotFoundException::class);

        $provider = new WebAccountUserProvider();
        $provider->loadUserByIdentifier('username');
    }

    /**
     * @throws Exception
     */
    public function testRefreshUserWithUnsupportedUser(): void
    {
        $this->expectException(UnsupportedUserException::class);

        $user = $this->createMock(UserInterface::class);
        $provider = new WebAccountUserProvider();
        $provider->refreshUser($user);

    }

    /**
     * @throws Exception
     */
    public function testRefreshUserWithSupportedUser(): void
    {
        $user = $this->createMock(User::class);
        $provider = new WebAccountUserProvider();
        $refreshedUser = $provider->refreshUser($user);
        $this->assertSame($user, $refreshedUser, 'The refreshed user should be the same as the original user.');
    }

    public function testSupportsClassWithUnsupportedClass(): void
    {
        $provider = new WebAccountUserProvider();
        $this->assertFalse($provider->supportsClass(\stdClass::class), 'The provider should not support stdClass.');
    }

    public function testSupportsClassWithSupportedClass(): void
    {
        $provider = new WebAccountUserProvider();
        $this->assertTrue($provider->supportsClass(User::class), 'The provider should support the User class.');
    }

}
