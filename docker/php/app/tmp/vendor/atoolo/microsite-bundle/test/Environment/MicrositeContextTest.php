<?php

declare(strict_types=1);

namespace Atoolo\Microsite\Test\Environment;

use Atoolo\Microsite\Environment\MicrositeContext;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MicrositeContext::class)]
class MicrositeContextTest extends TestCase
{
    private MicrositeContext $context;

    public function setUp(): void
    {
        $this->context = new MicrositeContext(
            resourceDir: '/var/www/example.com/www/resources',
            currentPath: '/test',
            micrositeHost: 'www.test.com',
            micrositePath: '/microsite/blue',
            mainHost: 'example.com',
            siteId: 243,
            mountableObjectTypes: ['foo', 'bar'],
        );
    }

    public function testIsMicrositePath(): void
    {
        $this->asserttrue($this->context->isMicrositePath('/microsite/blue/bar'));
    }

    public function testIsMicrositePathWithNonMicrositePath(): void
    {
        $this->assertFalse($this->context->isMicrositePath('/microsite/foo'));
    }

    public function testMicrositePathEndsWithSlash(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Microsite path must not end with a slash');

        new MicrositeContext(
            resourceDir: '/resource/dir',
            currentPath: '/current/path',
            micrositeHost: 'www.test.com',
            micrositePath: '/microsite/path/',
            mainHost: 'main.host',
            siteId: 1,
            mountableObjectTypes: ['type1', 'type2'],
        );
    }

}
