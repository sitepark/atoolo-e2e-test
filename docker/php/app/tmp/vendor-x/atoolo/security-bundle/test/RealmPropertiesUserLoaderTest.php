<?php

declare(strict_types=1);

namespace Atoolo\Security\Test;

use Atoolo\Security\Entity\User;
use Atoolo\Security\RealmPropertiesUserLoader;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * @covers \Atoolo\Security\RealmPropertiesUserLoader
 */
class RealmPropertiesUserLoaderTest extends TestCase
{
    private UserPasswordHasherInterface $passwordHasher;

    private RealmPropertiesUserLoader $loader;

    private static string $BASE_DIR = __DIR__ .
        '/resources/RealmPropertiesUserLoaderTest';

    public function setUp(): void
    {
        $this->passwordHasher = $this->createStub(
            UserPasswordHasherInterface::class,
        );
        $this->passwordHasher->method('hashPassword')
            ->willReturnCallback(
                function (
                    PasswordAuthenticatedUserInterface $user,
                    string $plainPassword,
                ) {
                    return "hash:" . $plainPassword;
                },
            );
        $this->passwordHasher->method('isPasswordValid')
            ->willReturn(true);
        $this->passwordHasher->method('needsRehash')
            ->willReturn(false);

        $this->loader = new RealmPropertiesUserLoader(
            self::$BASE_DIR . '/realm.properties',
            $this->passwordHasher,
        );
    }

    public function testLoaderUser(): void
    {
        $users = $this->loader->load();
        $expected = [
            'api' => $this->createUser(
                'api',
                'develop',
                ['ROLE_API', 'ROLE_TEST'],
            ),
            'no-roles' => $this->createUser(
                'no-roles',
                'develop',
                [],
            ),
            'empty-password' => $this->createUser(
                'empty-password',
                '',
                [],
            ),
            'role-with-space' => $this->createUser(
                'role-with-space',
                'develop',
                ['ROLE_TEST1', 'ROLE_TEST2'],
            ),
        ];
        $this->assertEquals(
            $expected,
            $users,
            'The loaded users do not match the expected users',
        );
    }

    public function testPasswordCallback(): void
    {
        $users = $this->loader->load();
        $user = $users['api'];
        $this->assertEquals(
            'hash:develop',
            $user->getPassword(),
            'The password was not hashed correctly',
        );
    }

    public function testLoadMissingFile(): void
    {
        $passwordHasher = $this->createStub(
            UserPasswordHasherInterface::class,
        );
        $loader = new RealmPropertiesUserLoader(
            '/missing-file',
            $passwordHasher,
        );

        $this->expectException(RuntimeException::class);
        $loader->load();
    }

    public function testLoadUnreadableFile(): void
    {
        $passwordHasher = $this->createStub(
            UserPasswordHasherInterface::class,
        );
        @mkdir('./var/test/');
        $unreadable = './var/test/unreadable';
        touch($unreadable);
        chmod($unreadable, 0000);
        $loader = new RealmPropertiesUserLoader(
            $unreadable,
            $passwordHasher,
        );

        $this->expectException(RuntimeException::class);

        try {
            $loader->load();
        } finally {
            chmod($unreadable, 0755);
            unlink($unreadable);
        }
    }

    /**
     * @param non-empty-string $username
     * @param list<string> $roles
     */
    private function createUser(
        string $username,
        string $plaintextPassword,
        array $roles,
    ): User {
        $user = new User($username, $roles);
        $user->setPasswordCallback(
            function () use ($plaintextPassword, $user) {
                return $this->passwordHasher->hashPassword(
                    $user,
                    $plaintextPassword,
                );
            },
        );
        return $user;
    }
}
