<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Check\Test\Console\Command;

use Atoolo\Runtime\Check\Console\Command\CheckCommand;
use Atoolo\Runtime\Check\Service\CheckStatus;
use Atoolo\Runtime\Check\Service\Cli\RuntimeCheck;
use Atoolo\Runtime\Check\Service\RuntimeStatus;
use Atoolo\Runtime\Check\Service\RuntimeType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(CheckCommand::class)]
class CheckCommandTest extends TestCase
{
    private CommandTester $commandTester;

    private RuntimeCheck $runtimeCheck;

    public function setUp(): void
    {
        $this->runtimeCheck = $this->createStub(
            RuntimeCheck::class,
        );

        $command = new CheckCommand(
            $this->runtimeCheck,
        );

        $this->commandTester = new CommandTester($command);
    }

    public function testExecuteSuccess(): void
    {
        $checkStatus = CheckStatus::createSuccess();
        $checkStatus->addReport('test', ['a' => 'b']);
        $runtimeStatus = new RuntimeStatus();
        $runtimeStatus->addStatus(RuntimeType::CLI, $checkStatus);
        $this->runtimeCheck->method('execute')->willReturn(
            $runtimeStatus,
        );

        $this->commandTester->execute([]);
        $this->assertEquals(
            <<<EOF
cli/test
{
    "a": "b"
}

Success

EOF,
            $this->commandTester->getDisplay(),
            'Command should display failure message',
        );
    }

    public function testExecuteFailure(): void
    {
        $status = CheckStatus::createFailure();
        $status->addMessage('test', 'test message');

        $runtimeStatus = new RuntimeStatus();
        $runtimeStatus->addStatus(RuntimeType::CLI, $status);

        $this->runtimeCheck->method('execute')->willReturn(
            $runtimeStatus,
        );

        $this->commandTester->execute([]);
        $this->assertEquals(
            <<<EOF
Failure
cli/test: test message

EOF,
            $this->commandTester->getDisplay(),
            'Command should display failure message',
        );
    }

    public function testJsonResult(): void
    {
        $checkStatus = CheckStatus::createSuccess();
        $checkStatus->addReport('test', ['a' => 'b']);
        $runtimeStatus = new RuntimeStatus();
        $runtimeStatus->addStatus(RuntimeType::CLI, $checkStatus);
        $this->runtimeCheck->method('execute')->willReturn(
            $runtimeStatus,
        );

        $this->commandTester->execute(
            ['--json' => true],
        );
        $this->assertEquals(
            <<<EOF
{
    "cli": {
        "success": true,
        "reports": {
            "test": {
                "a": "b"
            }
        }
    },
    "success": true
}

EOF,
            $this->commandTester->getDisplay(),
            'Command should display json result',
        );
    }
}
