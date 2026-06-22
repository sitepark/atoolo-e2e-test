<?php

declare(strict_types=1);

namespace Atoolo\Security;

use Atoolo\Security\Entity\User;
use Atoolo\Security\SiteKit\RoleMapper;
use RuntimeException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class RealmPropertiesUserLoader implements UserLoader
{
    /**
     * @var UserPasswordHasherInterface
     */
    private $passwordHasher;

    /**
     * @var string
     */
    private $realmPropertiesFile;

    public function __construct(
        string $realmPropertiesFile,
        UserPasswordHasherInterface $passwordHasher,
    ) {
        $this->realmPropertiesFile = $realmPropertiesFile;
        $this->passwordHasher      = $passwordHasher;
    }

    /**
     * @return array<string, UserInterface&PasswordAuthenticatedUserInterface>
     */
    public function load(): array
    {
        $userList = [];
        $realm = $this->loadRealm();
        foreach ($realm as $name => $values) {
            $user = $this->createUser($name, $values);
            $userList[$user->getUserIdentifier()] = $user;
        }

        return $userList;
    }

    /**
     * @return array<non-empty-string, array<string>>
     */
    private function loadRealm(): array
    {
        $realm = [];
        if (!file_exists($this->realmPropertiesFile)) {
            throw new RuntimeException(
                'Realm properties file not found: ' . $this->realmPropertiesFile,
            );
        }
        $content = @file_get_contents($this->realmPropertiesFile);
        if ($content === false) {
            throw new RuntimeException(
                'Unable to load ' . $this->realmPropertiesFile,
            );
        }

        $lines = preg_split("/((\r?\n)|(\r\n?))/", $content) ?: [];

        foreach ($lines as $line) {
            if (str_starts_with($line, ';') || str_starts_with($line, '#')) {
                continue;
            }
            $parts = explode(':', $line);
            if (count($parts) !== 2) {
                continue;
            }
            $user = $parts[0];
            if (empty($user)) {
                continue;
            }
            $values = trim($parts[1]);
            if (empty($values)) {
                $realm[$user] = [];
                continue;
            }
            $realm[$user] = explode(',', $values);
        }

        return $realm;
    }

    /**
     * @param non-empty-string $name
     * @param array<string> $values
     */
    private function createUser(string $name, array $values): User
    {
        if (empty($values)) {
            $plaintextPassword = '';
        } else {
            $plaintextPassword = trim($values[0]);
        }
        array_shift($values);
        $roles = RoleMapper::map($values);
        $user = new User($name, $roles);

        /**
         * The password is provided here via a callback, as the realm.properties
         * contains the password in plain text. However, the User:setPassword()
         * expects a hashed password. hashing the password is very
         * time-consuming. The callback ensures that the password is only hashed
         * if it is necessary. It is only necessary for an authentication
         * process.It is not necessary when creating a user object via a JWT.
         * This optimization is precisely for this case.
         */
        $user->setPasswordCallback(function () use ($plaintextPassword, $user) {
            return $this->passwordHasher->hashPassword(
                $user,
                $plaintextPassword,
            );
        });

        return $user;
    }
}
