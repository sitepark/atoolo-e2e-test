<?php

declare(strict_types=1);

namespace Atoolo\Security\Test\Entity;

use Atoolo\Security\Entity\User;
use Atoolo\Security\Exception\SecurityException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(User::class)]
class UserTest extends TestCase
{
    public function testGetUserIdentifier(): void
    {
        $user = new User('test', []);
        $this->assertEquals(
            'test',
            $user->getUserIdentifier(),
            'User::getUserIdentifier should return the username',
        );
    }

    public function testGetRoles(): void
    {
        $user = new User('test', ['ROLE_TEST']);
        $this->assertEquals(
            ['ROLE_TEST'],
            $user->getRoles(),
            'User::getRoles should return the roles',
        );
    }

    public function testSetAndGetPassword(): void
    {
        $user = new User('test', []);
        $user->setPassword('develop');
        $this->assertEquals(
            'develop',
            $user->getPassword(),
            'User::setPassword should set the password',
        );
    }

    public function testSetPasswordCallback(): void
    {
        $user = new User('test', []);
        $user->setPasswordCallback(function () {
            return 'develop';
        });
        $this->assertEquals(
            'develop',
            $user->getPassword(),
            'getPassword should use the callback to get the password',
        );
    }

    public function testEraseCredentials(): void
    {
        $user = new User('test', []);
        $user->setPassword('develop');
        $user->eraseCredentials();
        $this->assertEquals(
            $user->getPassword(),
            'develop',
            'password should be the hashed password and must not be erased',
        );
    }

    public function testOfArray(): void
    {
        $data = [
            'username' => 'test',
            'password' => 'test',
            'roles' => ['test'],
        ];
        $user = User::ofArray($data);
        $expected = new User('test', ['ROLE_TEST']);
        $expected->setPassword('test');

        $this->assertEquals(
            $expected,
            $user,
            'User::ofArray should return a User object with the given data',
        );
    }

    public function testOfArrayMissingUsername(): void
    {
        $this->expectException(SecurityException::class);
        User::ofArray([
            'password' => 'test',
            'roles' => ['test'],
        ]);
    }

    public function testOfArrayMissingPassword(): void
    {
        $this->expectException(SecurityException::class);
        User::ofArray([
            'username' => 'test',
            'roles' => ['test'],
        ]);
    }

    public function testOfArrayMissingRoles(): void
    {
        $this->expectException(SecurityException::class);
        User::ofArray([
            'username' => 'test',
            'password' => 'test',
        ]);
    }
}
