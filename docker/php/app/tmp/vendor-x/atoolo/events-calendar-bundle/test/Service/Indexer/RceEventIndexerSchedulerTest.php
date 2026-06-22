<?php

declare(strict_types=1);

namespace Atoolo\EventsCalendar\Test\Service\Indexer;

use Atoolo\EventsCalendar\Dto\RceEvent\RceEventIndexEvent;
use Atoolo\EventsCalendar\Service\Indexer\RceEventIndexer;
use Atoolo\EventsCalendar\Service\Indexer\RceEventIndexerScheduler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RceEventIndexerScheduler::class)]
class RceEventIndexerSchedulerTest extends TestCase
{
    public function testGetSchedule(): void
    {

        $indexer = $this->createStub(RceEventIndexer::class);
        $rceEventIndexerScheduler = new RceEventIndexerScheduler(
            '* 2 * * *',
            $indexer,
        );

        $schedule = $rceEventIndexerScheduler->getSchedule();

        $this->assertEquals(
            1,
            count($schedule->getRecurringMessages()),
        );
    }

    public function testInvoke(): void
    {
        $indexer = $this->createMock(RceEventIndexer::class);
        $rceEventIndexerScheduler = new RceEventIndexerScheduler(
            '* 2 * * *',
            $indexer,
        );

        $indexer->expects($this->once())
            ->method('index');

        $rceEventIndexerScheduler->__invoke(new RceEventIndexEvent());
    }
}
