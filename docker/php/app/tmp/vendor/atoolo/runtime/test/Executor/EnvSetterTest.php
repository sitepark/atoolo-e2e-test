<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Test\Executor;

use Atoolo\Runtime\Executor\EnvSetter;
use Atoolo\Runtime\Executor\Platform;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(EnvSetter::class)]
class EnvSetterTest extends TestCase
{
    private string $resourceDir = __DIR__
        . '/../resources/Executor/EnvSetter';

    /**
     * @throws Exception
     */
    public function testSetEnv(): void
    {
        $platform = $this->createMock(Platform::class);
        $envSetter = new EnvSetter($platform);

        $platform->expects($this->once())
            ->method('putEnv')
            ->with('a', 'B')
            ->willReturn(true);

        $envSetter->execute('', [
            'package1' => [
                'env' => [
                    'set' => [
                        'a' => 'B',
                    ],
                ],
            ],
        ]);
    }

    public function testSetEnvTwiceWithSameValueAgain(): void
    {
        $platform = $this->createMock(Platform::class);
        $envSetter = new EnvSetter($platform);

        $platform->expects($this->once())
            ->method('putEnv')
            ->with('a', 'B')
            ->willReturn(true);

        $envSetter->execute('', [
            'package1' => [
                'env' => [
                    'set' => [
                        'a' => 'B',
                    ],
                ],
            ],
            'package2' => [
                'env' => [
                    'set' => [
                        'a' => 'B',
                    ],
                ],
            ],
        ]);
    }

    public function testSetIniTwiceWithDifferentValues(): void
    {
        $envSetter = new EnvSetter();

        $this->expectException(RuntimeException::class);
        $envSetter->execute('', [
            'package1' => [
                'env' => [
                    'set' => [
                        'a' => 'B',
                    ],
                ],
            ],
            'package2' => [
                'env' => [
                    'set' => [
                        'a' => 'C',
                    ],
                ],
            ],
        ]);
    }

    public function testSetIniWithNonString(): void
    {
        $envSetter = new EnvSetter();
        $this->expectException(RuntimeException::class);
        $envSetter->execute('', [
            'package1' => [
                'env' => [
                    'set' => [
                        'a' => 123,
                    ],
                ],
            ],
        ]);
    }

    public function testSetIniWithNull(): void
    {
        $platform = $this->createMock(Platform::class);
        $envSetter = new EnvSetter($platform);

        $platform->expects($this->never())
            ->method('putEnv');

        $envSetter->execute('', [
            'package1' => [
                'env' => [
                    'set' => [
                        'a' => null,
                    ],
                ],
            ],
        ]);
    }

    public function testPutEnvFailed(): void
    {
        $platform = $this->createMock(Platform::class);
        $envSetter = new EnvSetter($platform);

        $platform->method('putEnv')
            ->willReturn(false);

        $this->expectException(RuntimeException::class);
        $envSetter->execute('', [
            'package1' => [
                'env' => [
                    'set' => [
                        'a' => 'B',
                    ],
                ],
            ],
        ]);
    }

    public function testEnvironmentFileNotExists(): void
    {
        $platform = $this->createMock(Platform::class);
        $envSetter = new EnvSetter($platform);

        $platform->expects($this->never())
            ->method('readFile');

        $envSetter->execute('', [
            'package1' => [
                'env' => [
                    'file' => 'not-exists.file',
                ],
            ],
        ]);
    }

    public function testEnvironmentFileWithoutEnvironments(): void
    {
        $platform = $this->createMock(Platform::class);
        $envSetter = new EnvSetter($platform);

        $platform->expects($this->never())
            ->method('putEnv');

        $envSetter->execute('', [
            'package' => [
                'env' => [
                    'file' => $this->resourceDir
                        . '/no-environments',
                ],
            ],
        ]);
    }

    public function testWithoutEnvironmentFile(): void
    {
        $platform = $this->getMockBuilder(Platform::class)->onlyMethods(['putEnv'])->getMock();
        $envSetter = new EnvSetter($platform);

        $expected = [
            ['FOO', 'foo'],
            ['BAZ', 'qux'],
            ['test', 'Test'],
        ];
        $matcher = $this->exactly(count($expected));

        $platform->expects($matcher)
            ->method('putEnv')
            ->willReturnCallback(
                function (
                    string $key,
                    string $value,
                ) use (
                    $matcher,
                    $expected,
                ) {
                    $case = $matcher->numberOfInvocations();
                    $this->assertEquals(
                        $expected[$case - 1],
                        [$key, $value],
                        'unexpected env',
                    );
                    return true;
                },
            );

        $envSetter->execute('', [
            'package' => [
                'env' => [
                    'file' => $this->resourceDir . '/environments',
                    'set' => [
                        'FOO' => 'foo',
                        'test' => 'Test',
                    ],
                ],
            ],
        ]);
    }
}
