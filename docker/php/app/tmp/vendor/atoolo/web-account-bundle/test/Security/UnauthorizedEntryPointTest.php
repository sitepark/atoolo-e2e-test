<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Test\Security;

use Atoolo\WebAccount\Security\UnauthorizedEntryPoint;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

#[CoversClass(UnauthorizedEntryPoint::class)]
class UnauthorizedEntryPointTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testStart(): void
    {
        $entryPoint = new UnauthorizedEntryPoint("/account");
        $request = $this->createMock(Request::class);
        $response = $entryPoint->start($request);

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/account', $response->getTargetUrl());
    }
}
