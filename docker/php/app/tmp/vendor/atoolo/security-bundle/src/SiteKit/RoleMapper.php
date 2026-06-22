<?php

declare(strict_types=1);

namespace Atoolo\Security\SiteKit;

class RoleMapper
{
    /**
     * @param array<string> $roles
     * @return array<string>
     */
    public static function map(array $roles): array
    {

        return array_map(
            function ($role) {
                $role = trim($role);
                $role = strtoupper($role);
                return "ROLE_" . $role;
            },
            $roles,
        );
    }
}
