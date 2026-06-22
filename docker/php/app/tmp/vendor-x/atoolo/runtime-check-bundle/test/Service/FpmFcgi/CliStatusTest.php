<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Check\Test\Service\FpmFcgi;

use Atoolo\Runtime\Check\Service\FpmFcgi\CliStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CliStatus::class)]
class CliStatusTest extends TestCase
{
    private string $resourceDir = __DIR__
        . '/../../resources/Service/FpmFcgi/CliStatusTest';
    public function testExecuteSuccess(): void
    {
        $consoleBinPath = $this->resourceDir . '/console-success';
        $cliStatus = new CliStatus($consoleBinPath, 'resource-root');
        $checkStatus = $cliStatus->execute([]);
        $this->assertTrue(
            $checkStatus->success,
            'CheckStatus should be successful',
        );
    }

    public function testExecuteMissingCliStatus(): void
    {
        $consoleBinPath = $this->resourceDir . '/console-missing-cli';
        $cliStatus = new CliStatus($consoleBinPath, 'resource-root');
        $checkStatus = $cliStatus->execute([]);
        $this->assertFalse(
            $checkStatus->success,
            'CheckStatus should not be successful',
        );
    }

    public function testExecuteWithExitCode1(): void
    {
        $consoleBinPath = $this->resourceDir . '/console-exitcode-1';
        $cliStatus = new CliStatus($consoleBinPath, 'resource-root');
        $checkStatus = $cliStatus->execute([]);
        $this->assertFalse(
            $checkStatus->success,
            'CheckStatus should be successful',
        );
    }
}
