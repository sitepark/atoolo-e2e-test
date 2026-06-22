<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Check\Test\Console\Command\Io;

use Atoolo\Runtime\Check\Console\Command\Io\TypifiedInput;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Input\InputInterface;

#[CoversClass(TypifiedInput::class)]
class TypifiedInputTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testGetStringOption(): void
    {
        $symfonyInput = $this->createStub(InputInterface::class);
        $symfonyInput
            ->method('getOption')
            ->willReturn('abc');

        $input = new TypifiedInput($symfonyInput);

        $this->assertEquals(
            'abc',
            $input->getStringOption('a'),
            'unexpected option value',
        );
    }

    /**
     * @throws Exception
     */
    public function testGetStringMissingOption(): void
    {
        $symfonyInput = $this->createStub(InputInterface::class);
        $symfonyInput
            ->method('getOption')
            ->willReturn(null);

        $input = new TypifiedInput($symfonyInput);

        $this->assertNull(
            $input->getStringOption('a'),
            'unexpected option value',
        );
    }

    /**
     * @throws Exception
     */
    public function testGetStringOptWithInvalidValue(): void
    {
        $symfonyInput = $this->createStub(InputInterface::class);
        $symfonyInput
            ->method('getOption')
            ->willReturn(123);

        $input = new TypifiedInput($symfonyInput);

        $this->expectException(InvalidArgumentException::class);
        $input->getStringOption('a');
    }

    /**
     * @throws Exception
     */
    public function testGetBoolOption(): void
    {
        $symfonyInput = $this->createStub(InputInterface::class);
        $symfonyInput
            ->method('getOption')
            ->willReturn(true);

        $input = new TypifiedInput($symfonyInput);

        $this->assertEquals(
            true,
            $input->getBoolOption('a'),
            'unexpected option value',
        );
    }

    /**
     * @throws Exception
     */
    public function testGetBoolOptWithInvalidValue(): void
    {
        $symfonyInput = $this->createStub(InputInterface::class);
        $symfonyInput
            ->method('getOption')
            ->willReturn('abc');

        $input = new TypifiedInput($symfonyInput);

        $this->assertEquals(
            false,
            $input->getBoolOption('a'),
            'unexpected option value',
        );
    }

    /**
     * @throws Exception
     */
    public function testGetArrayOption(): void
    {
        $symfonyInput = $this->createStub(InputInterface::class);
        $symfonyInput
            ->method('getOption')
            ->willReturn(['abc']);

        $input = new TypifiedInput($symfonyInput);

        $this->assertEquals(
            ['abc'],
            $input->getArrayOption('a'),
            'unexpected option value',
        );
    }

    /**
     * @throws Exception
     */
    public function testGetArrayOptionMissingValue(): void
    {
        $symfonyInput = $this->createStub(InputInterface::class);
        $symfonyInput
            ->method('getOption')
            ->willReturn(null);

        $input = new TypifiedInput($symfonyInput);

        $this->assertEquals(
            [],
            $input->getArrayOption('a'),
            'unexpected option value',
        );
    }

    /**
     * @throws Exception
     */
    public function testGetArrayOptionInvaludValue(): void
    {
        $symfonyInput = $this->createStub(InputInterface::class);
        $symfonyInput
            ->method('getOption')
            ->willReturn('abc');

        $input = new TypifiedInput($symfonyInput);
        $this->expectException(InvalidArgumentException::class);
        $input->getArrayOption('a');
    }
}
