<?php

declare(strict_types=1);

namespace Atoolo\Deployment\Test\Service;

use Atoolo\Deployment\Service\Deployer;
use Atoolo\Deployment\Service\DeploymentExecutable;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Deployer::class)]
class DeployerTest extends TestCase
{
    public function testExecute(): void
    {
        $task = $this->createMock(DeploymentExecutable::class);
        $logger = $this->createStub(\Psr\Log\LoggerInterface::class);
        $deployer = new Deployer([$task], $logger);

        $task->expects($this->once())
            ->method('execute');

        $this->assertTrue($deployer->execute());
    }

    public function testExecuteFailed(): void
    {
        $task = $this->createStub(DeploymentExecutable::class);
        $logger = $this->createMock(\Psr\Log\LoggerInterface::class);
        $deployer = new Deployer([$task], $logger);

        $task->method('execute')
            ->willThrowException(new \Exception('Task failed'));

        $logger->expects($this->once())
            ->method('warning');

        $this->assertFalse($deployer->execute());
    }
}
