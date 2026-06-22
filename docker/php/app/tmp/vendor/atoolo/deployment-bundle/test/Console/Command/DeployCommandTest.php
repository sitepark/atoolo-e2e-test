<?php

declare(strict_types=1);

namespace Atoolo\Deployment\Test\Console\Command;

use Atoolo\Deployment\Console\Command\DeployCommand;
use Atoolo\Deployment\Service\Deployer;
use Atoolo\Search\Console\Application;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(DeployCommand::class)]
class DeployCommandTest extends TestCase
{
    private Deployer&MockObject $deployer;
    private CommandTester $commandTester;

    public function setUp(): void
    {
        $this->deployer = $this->createMock(Deployer::class);
        $this->deployer->method('execute')
            ->willReturn(true);

        $command = new DeployCommand($this->deployer);

        $this->commandTester = new CommandTester($command);
    }

    public function testExecute(): void
    {
        $this->deployer
            ->expects($this->once())
            ->method('execute')
            ->willReturn(true);
        $this->commandTester->execute([]);
    }
}
