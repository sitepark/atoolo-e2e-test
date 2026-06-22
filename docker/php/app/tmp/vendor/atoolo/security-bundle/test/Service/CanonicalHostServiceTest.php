<?php

declare(strict_types=1);

namespace Atoolo\Security\Test\Service;

use Atoolo\Resource\DataBag;
use Atoolo\Resource\ResourceChannel;
use Atoolo\Resource\ResourceTenant;
use Atoolo\Security\Service\CanonicalHostProvider;
use Atoolo\Security\Service\CanonicalHostService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CanonicalHostService::class)]
class CanonicalHostServiceTest extends TestCase
{
    private CanonicalHostService $canonicalHostService;

    private CanonicalHostProvider $canonicalHostProvider;

    protected function setUp(): void
    {
        $this->canonicalHostProvider = $this->createMock(CanonicalHostProvider::class);
        $resourceTanent = $this->createMock(ResourceTenant::class);
        $resourceChannel = new ResourceChannel(
            '',
            '',
            '',
            'www.test.com',
            false,
            '',
            '',
            '',
            '',
            '',
            '',
            [],
            new DataBag([]),
            $resourceTanent,
        );
        $this->canonicalHostService = new CanonicalHostService($resourceChannel, [$this->canonicalHostProvider]);
    }

    public function testGetCanonicalHostFromProvider(): void
    {
        $this->canonicalHostProvider->expects($this->once())
            ->method('getCanonicalHost')
            ->willReturn('canonical.test.com');

        $canonicalHost = $this->canonicalHostService->getCanonicalHost();

        $this->assertEquals('canonical.test.com', $canonicalHost, 'Canonical host should be returned from provider');
    }

    public function testGetCanonicalHostFromResourceChannel(): void
    {
        $this->canonicalHostProvider->expects($this->once())
            ->method('getCanonicalHost')
            ->willReturn(null);

        $canonicalHost = $this->canonicalHostService->getCanonicalHost();

        $this->assertEquals('www.test.com', $canonicalHost, 'Canonical host should be returned from provider');
    }
}
