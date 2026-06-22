<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Test\Service;

use Atoolo\WebAccount\Service\CookieJar;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Cookie;

#[CoversClass(CookieJar::class)]
class CookieJarTest extends TestCase
{
    public function testAdd(): void
    {
        $cookieJar = new CookieJar();
        $cookie = new Cookie('test_cookie', 'test_value');

        $cookieJar->add($cookie);

        $this->assertSame($cookie, $cookieJar->all()[0]);
    }
}
