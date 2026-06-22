<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Test\Service;

use Atoolo\Resource\DataBag;
use Atoolo\Resource\ResourceChannel;
use Atoolo\Resource\ResourceTenant;
use Atoolo\WebAccount\Service\IesUrlResolver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(IesUrlResolver::class)]
class IesUrlResolverTest extends TestCase
{
    private readonly ResourceChannel $resourceChannel;

    public function setUp(): void
    {
        $resourceTenant = new ResourceTenant(
            "",
            "",
            "",
            "test.com",
            new DataBag([]),
        );

        $this->resourceChannel = new ResourceChannel(
            '',
            '',
            '',
            '',
            false,
            '',
            '',
            '',
            '',
            '',
            '',
            [],
            new DataBag([]),
            $resourceTenant,
        );
    }

    public function testFallbackToResourceChannelHost(): void
    {
        $iesUrlResolver = new IesUrlResolver(
            $this->resourceChannel,
            null,
            null,
            null,
        );

        $expectedUrl = 'https://' . $this->resourceChannel->tenant->host;
        $this->assertEquals($expectedUrl, $iesUrlResolver->getBaseUrl());
    }

    public function testWithCustomIesHost(): void
    {
        $iesUrlResolver = new IesUrlResolver(
            $this->resourceChannel,
            'https',
            'custom.ies.host',
            '443',
        );

        $expectedUrl = 'https://custom.ies.host:443';
        $this->assertEquals($expectedUrl, $iesUrlResolver->getBaseUrl());
    }
}
