<?php

declare(strict_types=1);

namespace Atoolo\Resource\Test\Exceptions;

use Atoolo\Resource\Exception\ResourceNotFoundException;
use Atoolo\Resource\ResourceLocation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ResourceNotFoundException::class)]
class ResourceNotFoundTest extends TestCase
{
    public function testGetLocation(): void
    {
        $e = new ResourceNotFoundException(ResourceLocation::of('abc'));
        $this->assertEquals(
            'abc',
            $e->getLocation()->location,
            'unexpected location',
        );
    }
}
