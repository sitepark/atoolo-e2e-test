<?php

declare(strict_types=1);

namespace Atoolo\Resource\Test;

use Atoolo\Resource\DataBag;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DataBag::class)]
class DataBagTest extends TestCase
{
    public function testHas(): void
    {
        $resource = new DataBag(
            ['field' => 'value'],
        );
        $this->assertTrue(
            $resource->has('field'),
            'should be found',
        );
    }

    public function testHasNot(): void
    {
        $resource = new DataBag(
            ['field' => 'value'],
        );
        $this->assertFalse(
            $resource->has('fieldx'),
            'should\'t be found',
        );
    }

    public function testGet(): void
    {
        $resource = new DataBag(
            ['field' => 'value'],
        );
        $this->assertEquals(
            ['field' => 'value'],
            $resource->get(),
            'unexpected value',
        );
    }

    public function testGetString(): void
    {
        $resource = new DataBag(
            ['field' => 'value'],
        );
        $this->assertEquals(
            'value',
            $resource->getString('field'),
            'unexpected value',
        );
    }

    public function testGetNestedString(): void
    {
        $resource = new DataBag(
            [
                'field' => [
                    'value' => 'test',
                ],
            ],
        );
        $this->assertEquals(
            'test',
            $resource->getString('field.value'),
            'unexpected value',
        );
    }

    public function testGetStringWithDefault(): void
    {
        $resource = new DataBag(
            ['field' => 'value'],
        );
        $this->assertEquals(
            'default',
            $resource->getString('fieldx', 'default'),
            'unexpected value',
        );
    }

    public function testGetDifferentType(): void
    {
        $resource = new DataBag(
            ['field' => '123'],
        );
        $this->assertEquals(
            0,
            $resource->getInt('field'),
            'expected value to be fallback of zero',
        );
    }

    public function testGetInt(): void
    {
        $resource = new DataBag(
            ['field' => 123],
        );
        $this->assertEquals(
            123,
            $resource->getInt('field'),
            'unexpected value',
        );
    }

    public function testGetIntWithDefault(): void
    {
        $resource = new DataBag(
            ['field' => 123],
        );
        $this->assertEquals(
            345,
            $resource->getInt('fieldx', 345),
            'unexpected value',
        );
    }

    public function testGetFloat(): void
    {
        $resource = new DataBag(
            ['field' => 1.23],
        );
        $this->assertEquals(
            1.23,
            $resource->getFloat('field'),
            'unexpected value',
        );
    }

    public function testGetFloatWithDefault(): void
    {
        $resource = new DataBag(
            ['field' => 1.23],
        );
        $this->assertEquals(
            3.45,
            $resource->getFloat('fieldx', 3.45),
            'unexpected value',
        );
    }

    public function testGetBool(): void
    {
        $resource = new DataBag(
            ['field' => true],
        );
        $this->assertTrue(
            $resource->getBool('field'),
            'unexpected value',
        );
    }

    public function testGetBoolWithDefault(): void
    {
        $resource = new DataBag(
            ['field' => true],
        );
        $this->assertTrue(
            $resource->getBool('fieldx', true),
            'unexpected value',
        );
    }

    public function testGetArray(): void
    {
        $resource = new DataBag(
            ['field' => ['a', 'b']],
        );
        $this->assertEquals(
            ['a', 'b'],
            $resource->getArray('field'),
            'unexpected value',
        );
    }

    public function testGetArrayWithDefault(): void
    {
        $resource = new DataBag(
            ['field' => ['a', 'b']],
        );
        $this->assertEquals(
            ['c', 'd'],
            $resource->getArray('fieldx', ['c', 'd']),
            'unexpected value',
        );
    }

    public function testGetAssociativeArray(): void
    {
        $resource = new DataBag(
            ['field' => ['a' => 'b']],
        );
        $this->assertEquals(
            ['a' => 'b'],
            $resource->getAssociativeArray('field'),
            'unexpected value',
        );
    }

    public function testGetAssociativeArrayWithDefault(): void
    {
        $resource = new DataBag(
            ['field' => ['a' => 'b']],
        );
        $this->assertEquals(
            ['c' => 'd'],
            $resource->getAssociativeArray('fieldx', ['c' => 'd']),
            'unexpected value',
        );
    }
}
