<?php

declare(strict_types=1);

namespace Atoolo\Microsite\Test\Service;

use Atoolo\Microsite\Environment\MicrositeContext;
use Atoolo\Microsite\Service\MicrositeUrlRewriteHandler;
use Atoolo\Microsite\Service\MountService;
use Atoolo\Rewrite\Dto\Url;
use Atoolo\Rewrite\Dto\UrlRewriterHandlerContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;

#[CoversClass(MicrositeUrlRewriteHandler::class)]
class MicrositeUrlRewriteHandlerTest extends TestCase
{
    private MicrositeContext&Stub $micrositeContext;

    private UrlRewriterHandlerContext&Stub $context;

    private MountService&MockObject $mountService;

    private MicrositeUrlRewriteHandler $handler;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->micrositeContext = $this->createStub(MicrositeContext::class);
        $this->context = $this->createStub(UrlRewriterHandlerContext::class);
        $this->mountService = $this->createMock(MountService::class);
        $this->handler = new MicrositeUrlRewriteHandler($this->micrositeContext, $this->mountService);
    }

    public function testRewriteWithNullMicrositeContext(): void
    {
        $url = Url::builder()->path('/path')->build();

        $handler = new MicrositeUrlRewriteHandler(null, null);
        $result = $handler->rewrite($url, $this->context);

        $this->assertSame($url, $result);
    }

    public function testRewriteWithNullPath(): void
    {
        $url = Url::builder()->build();
        $result = $this->handler->rewrite($url, $this->context);
        $this->assertSame($url, $result);
    }

    public function testRewriteWithNonNullHost(): void
    {
        $url = Url::builder()->scheme('https')->host('www.external.com')->path('/path')->build();
        $result = $this->handler->rewrite($url, $this->context);
        $this->assertSame($url, $result);
    }

    public function testRewriteWithMicrositePath(): void
    {
        $url = Url::builder()->path('/microsite/abc/path')->build();

        $micrositeContext = new MicrositeContext(
            resourceDir: '',
            currentPath: '/',
            micrositeHost: 'www.test.com',
            micrositePath: '/microsite/abc',
            mainHost: 'www.example.com',
            siteId: 123,
            mountableObjectTypes: [],
        );
        $handler = new MicrositeUrlRewriteHandler($micrositeContext, null);
        $result = $handler->rewrite($url, $this->context);
        $this->assertEquals('/path', $result->path);
    }

    public function testRewriteWithNullMountService(): void
    {
        $url = Url::builder()->path('/path')->build();

        $micrositeContext = new MicrositeContext(
            resourceDir: '',
            currentPath: '/',
            micrositeHost: 'www.test.com',
            micrositePath: '/microsite/abc',
            mainHost: 'www.example.com',
            siteId: 123,
            mountableObjectTypes: [],
        );

        $handler = new MicrositeUrlRewriteHandler($micrositeContext, null);
        $result = $handler->rewrite($url, $this->context);
        $this->assertSame($url, $result);
    }

    public function testRewriteWithNonMountablePath(): void
    {
        $url = Url::builder()->path('/path')->build();

        $micrositeContext = new MicrositeContext(
            resourceDir: '',
            currentPath: '/',
            micrositeHost: 'www.test.com',
            micrositePath: '/microsite/abc',
            mainHost: 'www.example.com',
            siteId: 123,
            mountableObjectTypes: [],
        );

        $this->mountService->method('isMountable')->willReturn(false);
        $handler = new MicrositeUrlRewriteHandler($micrositeContext, $this->mountService);
        $result = $handler->rewrite($url, $this->context);
        $expected = Url::builder()->scheme('https')->host('www.example.com')->path('/path')->build();
        $this->assertEquals($expected, $result);
    }

    public function testRewriteWithMountablePath(): void
    {

        $url = Url::builder()->path('/path')->build();

        $micrositeContext = new MicrositeContext(
            resourceDir: '',
            currentPath: '/',
            micrositeHost: 'www.test.com',
            micrositePath: '/microsite/abc',
            mainHost: 'www.example.com',
            siteId: 123,
            mountableObjectTypes: [],
        );

        $this->mountService->method('isMountable')->willReturn(true);
        $this->mountService->expects($this->once())->method('toMountedUrl');
        $handler = new MicrositeUrlRewriteHandler($micrositeContext, $this->mountService);

        $handler->rewrite($url, $this->context);
    }
}
