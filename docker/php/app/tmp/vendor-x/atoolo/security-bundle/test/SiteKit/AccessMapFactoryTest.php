<?php

declare(strict_types=1);

namespace Atoolo\Security\Test\SiteKit;

use Atoolo\Resource\DataBag;
use Atoolo\Resource\ResourceChannel;
use Atoolo\Resource\ResourceTenant;
use Atoolo\Security\SiteKit\AccessMapFactory;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Http\AccessMapInterface;

/**
 * @covers \Atoolo\Security\SiteKit\AccessMapFactory
 */
class AccessMapFactoryTest extends TestCase
{
    /*+
     * @covers AccessMapFactory
     */
    public function testDirNotExists(): void
    {
        $mockLogger = $this->createMock(LoggerInterface::class);
        $resourceChannel = $this->createResourceChannel(__DIR__ . '/security-not-exists');
        $factory = new AccessMapFactory($resourceChannel, $mockLogger);
        $accessMap = $factory->create();

        $this->assertInstanceOf(AccessMapInterface::class, $accessMap);
    }

    /*+
     * @covers AccessMapFactory
     */
    public function testLoadDir(): void
    {

        $mockLogger = $this->createMock(LoggerInterface::class);
        $mockLogger->expects($this->once())
            ->method('error');
        $resourceChannel = $this->createResourceChannel(__DIR__);

        $factory = new AccessMapFactory($resourceChannel, $mockLogger);
        $accessMap = $factory->create();

        $this->assertInstanceOf(AccessMapInterface::class, $accessMap);

        $request = Request::create('/path-a/index.php');
        $roles = $accessMap->getPatterns($request)[0];
        $this->assertIsArray($roles);
        $this->assertCount(1, $roles);
        $this->assertEquals(['ROLE_A'], $roles);

        $request = Request::create('/path-b/index.php');
        $roles = $accessMap->getPatterns($request)[0];
        $this->assertIsArray($roles);
        $this->assertCount(1, $roles);
        $this->assertEquals(['ROLE_B'], $roles);

        $request = Request::create('/path-c/index.php');
        $request->server->set('REMOTE_ADDR', '192.168.0.25');
        $roles = $accessMap->getPatterns($request)[0];
        $this->assertIsArray($roles);
        $this->assertCount(1, $roles);
        $this->assertEquals(['ROLE_C'], $roles);

        $request = Request::create('/path-c/index.php');
        $request->server->set('REMOTE_ADDR', '192.168.1.25');
        $roles = $accessMap->getPatterns($request)[0];
        $this->assertNull($roles);
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
