<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Check\Test\Service\Worker;

use Atoolo\Runtime\Check\Service\Checker\CheckerCollection;
use Atoolo\Runtime\Check\Service\CheckStatus;
use Atoolo\Runtime\Check\Service\Worker\WorkerCheckEvent;
use Atoolo\Runtime\Check\Service\Worker\WorkerCheckScheduler;
use Atoolo\Runtime\Check\Service\Worker\WorkerStatusFile;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Lock\LockFactory;

#[CoversClass(WorkerCheckScheduler::class)]
class WorkerCheckSchedulerTest extends TestCase
{
    public function testGetSchedule(): void
    {
        $workerStatusFile = new WorkerStatusFile('', 10);
        $checkerCollection = $this->createStub(CheckerCollection::class);
        $lockFactory = $this->createStub(LockFactory::class);
        $workerCheckScheduler = new WorkerCheckScheduler(
            $workerStatusFile,
            $checkerCollection,
            'www',
            $lockFactory,
        );

        $schedule = $workerCheckScheduler->getSchedule();

        $this->assertEquals(
            2,
            count($schedule->getRecurringMessages()),
        );
    }

    public function testInvoke(): void
    {
        $checkStatus = CheckStatus::createSuccess();

        $workerStatusFile = $this->createMock(WorkerStatusFile::class);
        $checkerCollection = $this->createStub(CheckerCollection::class);
        $checkerCollection->method('check')
            ->willReturn($checkStatus);
        $lockFactory = $this->createStub(LockFactory::class);
        $workerCheckScheduler = new WorkerCheckScheduler(
            $workerStatusFile,
            $checkerCollection,
            'www',
            $lockFactory,
        );


        $workerStatusFile->expects($this->once())
            ->method('write')
            ->with($checkStatus);
        $workerCheckScheduler->__invoke(new WorkerCheckEvent());
    }
}
