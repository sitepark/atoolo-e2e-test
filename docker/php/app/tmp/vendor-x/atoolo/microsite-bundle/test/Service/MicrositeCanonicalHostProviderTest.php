<?php

declare(strict_types=1);

namespace Atoolo\Microsite\Test\Service;

use Atoolo\Microsite\Environment\MicrositeContext;
use Atoolo\Microsite\Service\MicrositeCanonicalHostProvider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MicrositeCanonicalHostProvider::class)]
class MicrositeCanonicalHostProviderTest extends TestCase
{
    public function testGetCanonicalHostReturnsNullWhenMicrositeContextIsNull(): void
    {
        $provider = new MicrositeCanonicalHostProvider(null);
        $this->assertNull($provider->getCanonicalHost());
    }

    public function testGetCanonicalHostReturnsMicrositeHost(): void
    {
        $micrositeContext = new MicrositeContext(
            resourceDir: '',
            currentPath: '/',
            micrositeHost: 'abc.example.com',
            micrositePath: '/microsite/abc',
            mainHost: 'www.example.com',
            siteId: 123,
            mountableObjectTypes: [],
        );

        $provider = new MicrositeCanonicalHostProvider($micrositeContext);
        $this->assertEquals('abc.example.com', $provider->getCanonicalHost());
    }
}
