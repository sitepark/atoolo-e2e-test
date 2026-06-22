<?php

declare(strict_types=1);

namespace Atoolo\Resource\Test\Exceptions;

use Atoolo\Resource\Exception\RootMissingException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RootMissingException::class)]
class RootMissingExceptionTest extends TestCase
{
    public function testGetLocation(): void
    {
        $e = new RootMissingException('abc');
        $this->assertEquals(
            'abc',
            $e->getLocation(),
            'unexpected location',
        );
    }
}
