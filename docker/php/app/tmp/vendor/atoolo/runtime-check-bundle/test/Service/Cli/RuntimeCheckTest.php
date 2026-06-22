<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Check\Test\Service\Cli;

use Atoolo\Runtime\Check\Service\Checker\CheckerCollection;
use Atoolo\Runtime\Check\Service\CheckStatus;
use Atoolo\Runtime\Check\Service\Cli\FastCgiStatus;
use Atoolo\Runtime\Check\Service\Cli\FastCgiStatusFactory;
use Atoolo\Runtime\Check\Service\Cli\RuntimeCheck;
use Atoolo\Runtime\Check\Service\RuntimeStatus;
use Atoolo\Runtime\Check\Service\RuntimeType;
use Atoolo\Runtime\Check\Service\Worker\WorkerStatusFile;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

#[CoversClass(RuntimeCheck::class)]
class RuntimeCheckTest extends TestCase
{
    /**
     * @throws Exception
     * @throws \JsonException
     */
    public function testCheck(): void
    {
        $cliCheckStatus = CheckStatus::createSuccess()
            ->addReport('cli-status', ['a' => 'b']);
        $fastCgiCheckStatus = CheckStatus::createSuccess()
            ->addReport('fpm-status', ['a' => 'b']);
        $workerCheckStatus = CheckStatus::createSuccess()
            ->addReport('worker-status', ['a' => 'b']);
        $checkerCollection = $this->createStub(CheckerCollection::class);
        $checkerCollection->method('check')
            ->willReturn($cliCheckStatus);
        $fastCgiStatus = $this->createStub(FastCgiStatus::class);
        $fastCgiStatus->method('request')
            ->willReturn($fastCgiCheckStatus);
        $fastCgiStatusFactory = $this->createStub(FastCgiStatusFactory::class);
        $fastCgiStatusFactory->method('create')
            ->willReturn($fastCgiStatus);
        $workerStatusFile = $this->createStub(WorkerStatusFile::class);
        $workerStatusFile->method('read')
            ->willReturn($workerCheckStatus);

        $runtimeCheck = new RuntimeCheck(
            $checkerCollection,
            $fastCgiStatusFactory,
            $workerStatusFile,
        );
        $runtimeStatus = $runtimeCheck->execute([], null);

        $expected = new RuntimeStatus();
        $expected->addStatus(RuntimeType::CLI, $cliCheckStatus);
        $expected->addStatus(RuntimeType::FPM_FCGI, $fastCgiCheckStatus);
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
        $fastCgiStatus = $this->createStub(FastCgiStatus::class);
        $fastCgiStatusFactory = $this->createStub(FastCgiStatusFactory::class);
        $fastCgiStatusFactory->method('create')
            ->willReturn($fastCgiStatus);
        $workerStatusFile = $this->createStub(WorkerStatusFile::class);

        $runtimeCheck = new RuntimeCheck(
            $checkerCollection,
            $fastCgiStatusFactory,
            $workerStatusFile,
        );
        $runtimeStatus = $runtimeCheck->execute([
            RuntimeType::CLI->value,
            RuntimeType::FPM_FCGI->value,
            RuntimeType::WORKER->value,
        ], null);

        $expected = new RuntimeStatus();

        $this->assertEquals(
            $expected,
            $runtimeStatus,
            'Runtime status is not as expected',
        );
    }
}
