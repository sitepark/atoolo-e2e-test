<?php

declare(strict_types=1);

namespace Atoolo\Rewrite\Test\Service;

use Atoolo\Rewrite\Service\UrlRewriteContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

#[CoversClass(UrlRewriteContext::class)]
class UrlRewriteContextTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testConstructor(): void
    {
        $requestStack = $this->createMock(RequestStack::class);
        $request = $this->createMock(Request::class);
        $requestStack->expects($this->once())
            ->method('getCurrentRequest')
            ->willReturn($request);
        $request->expects($this->once())
            ->method('getScheme')
            ->willReturn('https');
        $request->expects($this->once())
            ->method('getHost')
            ->willReturn('example.com');
        $request->expects($this->once())
            ->method('getBasePath')
            ->willReturn('/base/path');

        $context = new UrlRewriteContext($requestStack);

        $this->assertSame('https', $context->getScheme());
        $this->assertSame('example.com', $context->getHost());
        $this->assertSame('/base/path', $context->getBasePath());
    }

    /**
     * @throws Exception
     */
    public function testSetScheme(): void
    {
        $requestStack = $this->createMock(RequestStack::class);
        $context = new UrlRewriteContext($requestStack);

        $context->setScheme('https');
        $this->assertSame('https', $context->getScheme());
    }

    /**
     * @throws Exception
     */
    public function testSetHost(): void
    {
        $requestStack = $this->createMock(RequestStack::class);
        $context = new UrlRewriteContext($requestStack);

        $context->setHost('example.com');
        $this->assertSame('example.com', $context->getHost());
    }

    /**
     * @throws Exception
     */
    public function testSetBasePath(): void
    {
        $requestStack = $this->createMock(RequestStack::class);
        $context = new UrlRewriteContext($requestStack);

        $context->setBasePath('/base/path');
        $this->assertSame('/base/path', $context->getBasePath());
    }

    /**
     * @throws Exception
     */
    public function testSetResourceLocation(): void
    {
        $requestStack = $this->createMock(RequestStack::class);
        $context = new UrlRewriteContext($requestStack);

        $context->setResourceLocation('/path');
        $this->assertSame('/path', $context->getResourceLocation());
    }

    public function testSetSameNavigation(): void
    {
        $requestStack = $this->createMock(RequestStack::class);
        $context = new UrlRewriteContext($requestStack);

        $context->setSameNavigation(true);
        $this->assertTrue($context->isSameNavigation());
    }

}
