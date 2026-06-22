<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Test\Executor;

use Atoolo\Runtime\Executor\IniSetter;
use Atoolo\Runtime\Executor\Platform;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(IniSetter::class)]
class IniSetterTest extends TestCase
{
    public function testSetIni(): void
    {
        $platform = $this->createMock(Platform::class);
        $iniSetter = new IniSetter($platform);

        $platform->expects($this->once())
            ->method('setIni')
            ->with('user_agent', 'Test')
            ->willReturn('');

        $iniSetter->execute('', [
            'package1' => [
                'ini' => [
                    'set' => [
                        'user_agent' => 'Test',
                    ],
                ],
            ],
        ]);
    }

    public function testSetIniTwiceWithSameValueAgain(): void
    {
        $platform = $this->createMock(Platform::class);
        $iniSetter = new IniSetter($platform);

        $platform->expects($this->once())
            ->method('setIni')
            ->with('user_agent', 'Test')
            ->willReturn('');


        $iniSetter->execute('', [
            'package1' => [
                'ini' => [
                    'set' => [
                        'user_agent' => 'Test',
                    ],
                ],
            ],
            'package2' => [
                'ini' => [
                    'set' => [
                        'user_agent' => 'Test',
                    ],
                ],
            ],
        ]);
    }

    public function testSetIniTwiceWithDifferentValues(): void
    {
        $iniSetter = new IniSetter();

        $this->expectException(RuntimeException::class);
        $iniSetter->execute('', [
            'package1' => [
                'ini' => [
                    'set' => [
                        'user_agent' => 'Test',
                    ],
                ],
            ],
            'package2' => [
                'ini' => [
                    'set' => [
                        'user_agent' => 'Test2',
                    ],
                ],
            ],
        ]);
    }

    public function testSetIniSystemDirective(): void
    {
        $iniSetter = new IniSetter();
        $this->expectException(RuntimeException::class);
        $iniSetter->execute('', [
            'package1' => [
                'ini' => [
                    'set' => [
                        'allow_url_fopen' => 0,
                    ],
                ],
            ],
        ]);
    }

    public function testSetIniWithNonScalar(): void
    {
        $iniSetter = new IniSetter();
        $this->expectException(RuntimeException::class);
        $iniSetter->execute('', [
            'package1' => [
                'ini' => [
                    'set' => [
                        'user_agent' => ['non' => 'scalar'],
                    ],
                ],
            ],
        ]);
    }

    public function testSetIniWithNull(): void
    {
        $platform = $this->createMock(Platform::class);
        $iniSetter = new IniSetter($platform);

        $platform->expects($this->never())
            ->method('setIni');

        $iniSetter->execute('', [
            'package1' => [
                'ini' => [
                    'set' => [
                        'user_agent' => null,
                    ],
                ],
            ],
        ]);
    }

    public function testSetIniFailed(): void
    {
        $platform = $this->createMock(Platform::class);
        $iniSetter = new IniSetter($platform);

        $platform->expects($this->once())
            ->method('setIni')
            ->with('user_agent', 'Test')
            ->willReturn(false);
        $this->expectException(RuntimeException::class);
        $iniSetter->execute('', [
            'package1' => [
                'ini' => [
                    'set' => [
                        'user_agent' => 'Test',
                    ],
                ],
            ],
        ]);
    }
}
