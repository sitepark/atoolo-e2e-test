<?php

declare(strict_types=1);

namespace Atoolo\Security\Test\SiteKit;

use Atoolo\Resource\DataBag;
use Atoolo\Resource\ResourceChannel;
use Atoolo\Resource\ResourceTenant;
use Atoolo\Security\SiteKit\UserLoader;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

/**
 * @covers \Atoolo\Security\SiteKit\UserLoader
 */
class UserLoaderTest extends TestCase
{
    /*+
     * @covers UserLoader
     */
    public function testDirNotExists(): void
    {

        $mockLogger = $this->createMock(LoggerInterface::class);
        $resourceChannel = $this->createResourceChannel(__DIR__ . '/security-not-exists');

        $loader = new UserLoader($resourceChannel, $mockLogger);
        $users = $loader->load();

        $this->assertIsArray($users);
        $this->assertEmpty($users);
    }

    /*+
     * @covers UserLoader
     */
    public function testLoadDir(): void
    {

        $mockLogger = $this->createMock(LoggerInterface::class);
        $mockLogger->expects($this->once())
            ->method('error');

        $resourceChannel = $this->createResourceChannel(__DIR__);

        $loader = new UserLoader($resourceChannel, $mockLogger);
        $users = $loader->load();

        $this->assertIsArray($users);
        $this->assertCount(3, $users);

        $this->assertEquals('a', $users['a']->getUserIdentifier());
        $this->assertEquals("hash:a", $users['a']->getPassword());
        $this->assertEquals(['ROLE_USER'], $users['a']->getRoles());

        $this->assertEquals('b', $users['b']->getUserIdentifier());
        $this->assertEquals("hash:b", $users['b']->getPassword());
        $this->assertEquals(
            ['ROLE_USER', 'ROLE_ADMIN'],
            $users['b']->getRoles(),
        );

        $this->assertEquals('x', $users['x']->getUserIdentifier());
        $this->assertEquals("hash:x", $users['x']->getPassword());
        $this->assertEquals(['ROLE_XER'], $users['x']->getRoles());
    }

    private function createResourceChannel(string $resourceDir): ResourceChannel
    {
        $resourceTanent = $this->createMock(ResourceTenant::class);
        return new ResourceChannel(
            '',
            '',
            '',
            '',
            false,
            '',
            '',
            '',
            $resourceDir,
            '',
            '',
            [],
            new DataBag([]),
            $resourceTanent,
        );
    }

}
