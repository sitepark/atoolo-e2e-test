<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Test\Service;

use Atoolo\WebAccount\Service\ApplyCookieJarListener;
use Atoolo\WebAccount\Service\CookieJar;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

#[CoversClass(ApplyCookieJarListener::class)]
class ApplyCookieJarListenerTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testOnKernelResponse(): void
    {
        $cookieJar = $this->createMock(CookieJar::class);
        $cookie = new Cookie('test_cookie', 'test_value');

        $cookieJar->expects($this->once())
            ->method('all')
            ->willReturn([$cookie]);

        $listener = new ApplyCookieJarListener($cookieJar);

        $response = new Response();
        $responseEvent = new ResponseEvent(
            $this->createMock(HttpKernelInterface::class),
            $this->createMock(Request::class),
            HttpKernelInterface::MAIN_REQUEST,
            $response,
        );

        $listener->onKernelResponse($responseEvent);

        $this->assertEquals(
            'test_value',
            $response->headers->getCookies()[0]->getValue(),
            'Cookie should be set in the response headers',
        );
    }
}
