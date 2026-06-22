<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Test\Executor;

use Atoolo\Runtime\Executor\Platform;
use Atoolo\Runtime\Executor\UmaskSetter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(UmaskSetter::class)]
class UmaskSetterTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testSetUmask(): void
    {
        $platform = $this->createMock(Platform::class);
        $umaskSetter = new UmaskSetter($platform);

        $platform->expects($this->once())
            ->method('umask')
            ->with(123)
            ->willReturn(123);
        $umaskSetter->execute('', [
            'package1' => [
                'umask' => '0123',
            ],
        ]);
    }

    /**
     * @throws Exception
     */
    public function testSetUmaskWithoutUmask(): void
    {
        $platform = $this->createMock(Platform::class);
        $umaskSetter = new UmaskSetter($platform);

        $platform->expects($this->never())
            ->method('umask');

        $umaskSetter->execute('', [
            'package1' => [
            ],
        ]);
    }

    /**
     * @throws Exception
     */
    public function testSetUmaskTwiceWithSameValue(): void
    {
        $platform = $this->createMock(Platform::class);
        $umaskSetter = new UmaskSetter($platform);

        $platform->expects($this->once())
            ->method('umask')
            ->with(123)
            ->willReturn(123);

        $umaskSetter->execute('', [
            'package1' => [
                'umask' => '0123',
            ],
            'package2' => [
                'umask' => '0123',
            ],
        ]);
    }

    public function testSetUmaskTwiceWithDifferentValues(): void
    {

        $umaskSetter = new UmaskSetter();

        $this->expectException(RuntimeException::class);
        $umaskSetter->execute('', [
            'package1' => [
                'umask' => '0123',
            ],
            'package2' => [
                'umask' => '0456',
            ],
        ]);
    }

    public function testSetUmaskWithNonNumericValue(): void
    {
        $umaskSetter = new UmaskSetter();
        $this->expectException(RuntimeException::class);
        $umaskSetter->execute('', [
            'package1' => [
                'umask' => 'abc',
            ],
        ]);
    }
}
