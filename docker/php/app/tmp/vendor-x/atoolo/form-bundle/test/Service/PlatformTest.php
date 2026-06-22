<?php

declare(strict_types=1);

namespace Atoolo\Form\Test\Service;

use Atoolo\Form\Service\Platform;
use DateTime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Platform::class)]
class PlatformTest extends TestCase
{
    public function testDatetime(): void
    {
        $platform = new Platform();
        $this->expectNotToPerformAssertions();
        $platform->datetime();
    }

    public function testObjectToArrayRecursive(): void
    {
        $platform = new Platform();
        $this->assertEquals(
            ['a' =>
                [
                    'b' => ['c' => 'd'],
                    'e' => [
                        [ 'f' => 'g' ],
                    ],
                ],
            ],
            $platform->objectToArrayRecursive((object) [
                'a' => (object) [
                    'b' => (object) ['c' => 'd'],
                    'e' => [
                        (object) ['f' => 'g'],
                    ],
                ],
            ]),
            'unexpected value',
        );
    }

    public function testArrayToObjectRecursive(): void
    {
        $platform = new Platform();
        $this->assertEquals(
            (object) ['a' => (object) ['b' => (object) ['c' => 'd']]],
            $platform->arrayToObjectRecursive(['a' => ['b' => ['c' => 'd']]]),
            'unexpected value',
        );
    }
}
