<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Executor;

use RuntimeException;

class UserValidator implements RuntimeExecutor
{
    public function execute(string $projectDir, array $options): void
    {
        $validUsers = $this->getValidUsers($options);
        $this->validateUser($validUsers);
    }

    /**
     * @param RuntimeOptions $options
     * @return array<string>
     */
    private function getValidUsers(array $options): array
    {
        $validUser = [];

        foreach ($options as $package => $packageOptions) {
            if (!isset($packageOptions['users'])) {
                continue;
            }
            $users = $packageOptions['users'];
            if (!is_array($users)) {
                throw new RuntimeException(
                    "[atoolo.runtime.users]: '
                    . 'users from package $package should be an array: $users",
                );
            }

            $validUser = [];
            foreach ($users as $user) {
                if ($user === '{SCRIPT_OWNER}') {
                    // owner (name) of the current script
                    $validUser[] = get_current_user();
                    // owner (uid) of the current script
                    $validUser[] = (string) getmyuid();
                    continue;
                }
                $validUser[] = $user;
            }
        }

        return $validUser;
    }

    /**
     * @param array<string> $validUsers
     */
    private function validateUser(array $validUsers): void
    {
        if (empty($validUsers)) {
            return;
        }
        $processUser = (posix_getpwuid(posix_geteuid())
            ?: ['name' => posix_geteuid()])['name'];
        if (!in_array($processUser, $validUsers)) {
            throw new RuntimeException(
                "[atoolo.runtime.users]: "
                . "The current user '$processUser'"
                . " is not valid. Valid users are: "
                . implode(', ', $validUsers),
            );
        }
    }
}
