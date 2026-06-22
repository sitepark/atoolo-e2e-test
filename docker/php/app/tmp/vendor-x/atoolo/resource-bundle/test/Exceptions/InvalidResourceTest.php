<?php

declare(strict_types=1);

namespace Atoolo\Resource\Test\Exceptions;

use Atoolo\Resource\Exception\InvalidResourceException;
use Atoolo\Resource\ResourceLocation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvalidResourceException::class)]
class InvalidResourceTest extends TestCase
{
    public function testGetLocation(): void
    {
        $e = new InvalidResourceException(ResourceLocation::of('abc'));
        $this->assertEquals(
            'abc',
            $e->getLocation()->location,
            'unexpected location',
        );
    }
}
