<?php

declare(strict_types=1);

namespace Atoolo\Security\Test\SiteKit;

use Atoolo\Security\SiteKit\RoleMapper;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RoleMapper::class)]
class RoleMapperTest extends TestCase
{
    public function testMap(): void
    {
        $roles = ['admin', 'user'];
        $mappedRoles = RoleMapper::map($roles);
        $this->assertEquals(
            ['ROLE_ADMIN', 'ROLE_USER'],
            $mappedRoles,
            'Roles should be mapped',
        );
    }
}
