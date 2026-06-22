<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Check\Test\Service\FpmFcgi;

use Atoolo\Runtime\Check\Service\Checker\CheckerCollection;
use Atoolo\Runtime\Check\Service\CheckStatus;
use Atoolo\Runtime\Check\Service\FpmFcgi\CliStatus;
use Atoolo\Runtime\Check\Service\FpmFcgi\RuntimeCheck;
use Atoolo\Runtime\Check\Service\RuntimeStatus;
use Atoolo\Runtime\Check\Service\RuntimeType;
use Atoolo\Runtime\Check\Service\Worker\WorkerStatusFile;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RuntimeCheck::class)]
class RuntimeCheckTest extends TestCase
{
    public function testCheck(): void
    {
        $fpmCheckStatus = CheckStatus::createSuccess()
            ->addReport('fpm-status', ['a' => 'b']);
        $cliCheckStatus = CheckStatus::createSuccess()
            ->addReport('cli-status', ['a' => 'b']);
        $workerCheckStatus = CheckStatus::createSuccess()
            ->addReport('worker-status', ['a' => 'b']);
        $checkerCollection = $this->createStub(CheckerCollection::class);
        $checkerCollection->method('check')
            ->willReturn($fpmCheckStatus);
        $cliStatus = $this->createStub(CliStatus::class);
        $cliStatus->method('execute')
            ->willReturn($cliCheckStatus);
        $workerStatusFile = $this->createStub(WorkerStatusFile::class);
        $workerStatusFile->method('read')
            ->willReturn($workerCheckStatus);

        $runtimeCheck = new RuntimeCheck(
            $checkerCollection,
            $cliStatus,
            $workerStatusFile,
        );
        $runtimeStatus = $runtimeCheck->execute([]);

        $expected = new RuntimeStatus();
        $expected->addStatus(RuntimeType::FPM_FCGI, $fpmCheckStatus);
        $expected->addStatus(RuntimeType::CLI, $cliCheckStatus);
        $expected->addStatus(RuntimeType::WORKER, $workerCheckStatus);

        $this->assertEquals(
            $expected,
            $runtimeStatus,
            'Runtime status is not as expected',
        );
    }

    public function testCheckSkip(): void
    {
        $checkerCollection = $this->createStub(CheckerCollection::class);
        $cliStatus = $this->createStub(CliStatus::class);
        $workerStatusFile = $this->createStub(WorkerStatusFile::class);

        $runtimeCheck = new RuntimeCheck(
            $checkerCollection,
            $cliStatus,
            $workerStatusFile,
        );
        $runtimeStatus = $runtimeCheck->execute([
            RuntimeType::FPM_FCGI->value,
            RuntimeType::CLI->value,
            RuntimeType::WORKER->value,
        ]);

        $expected = new RuntimeStatus();

        $this->assertEquals(
            $expected,
            $runtimeStatus,
            'Runtime status is not as expected',
        );
    }
}
