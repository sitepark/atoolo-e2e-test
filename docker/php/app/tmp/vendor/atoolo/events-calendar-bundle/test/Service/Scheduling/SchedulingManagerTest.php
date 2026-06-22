<?php

declare(strict_types=1);

namespace Atoolo\EventsCalendar\Test\Service\Scheduling;

use Atoolo\EventsCalendar\Dto\Scheduling\Scheduling;
use Atoolo\EventsCalendar\Service\Scheduling\SchedulingManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SchedulingManager::class)]
class SchedulingManagerTest extends TestCase
{
    private SchedulingManager $schedulingManager;

    public function setup(): void
    {
        $this->schedulingManager = new SchedulingManager();
    }

    public function testGetOccurrences(): void
    {
        // multiday, every monday, 3 times
        $scheduling = new Scheduling(
            new \DateTime('01.01.2024 12:00'),
            new \DateTime('01.01.2024 22:00'),
            false,
            true,
            true,
            'FREQ=WEEKLY;INTERVAL=1;BYDAY=MO;COUNT=3',
        );
        $this->assertEquals(
            [
                new Scheduling(
                    new \DateTime('01.01.2024 12:00'),
                    new \DateTime('01.01.2024 22:00'),
                    false,
                    true,
                    true,
                    null,
                ),
                new Scheduling(
                    new \DateTime('08.01.2024 12:00'),
                    new \DateTime('08.01.2024 22:00'),
                    false,
                    true,
                    true,
                    null,
                ),
                new Scheduling(
                    new \DateTime('15.01.2024 12:00'),
                    new \DateTime('15.01.2024 22:00'),
                    false,
                    true,
                    true,
                    null,
                ),
            ],
            $this->schedulingManager->getAllOccurrencesOfScheduling($scheduling),
        );
    }

    public function testGetOccurrencesWithoutEnd(): void
    {
        // multiday, every monday, 3 times
        $scheduling = new Scheduling(
            new \DateTime('01.01.2024 12:00'),
            null,
            false,
            true,
            false,
            'FREQ=WEEKLY;INTERVAL=1;BYDAY=MO;COUNT=3',
        );
        $this->assertEquals(
            [
                new Scheduling(
                    new \DateTime('01.01.2024 12:00'),
                    null,
                    false,
                    true,
                    false,
                    null,
                ),
                new Scheduling(
                    new \DateTime('08.01.2024 12:00'),
                    null,
                    false,
                    true,
                    false,
                    null,
                ),
                new Scheduling(
                    new \DateTime('15.01.2024 12:00'),
                    null,
                    false,
                    true,
                    false,
                    null,
                ),
            ],
            $this->schedulingManager->getAllOccurrencesOfScheduling($scheduling),
        );
    }

    public function testGetOccurrencesWithoutRRule(): void
    {
        $scheduling = new Scheduling(
            new \DateTime('01.01.2024 12:00'),
            null,
            false,
            true,
            false,
            null,
        );
        $this->assertEquals(
            [
                new Scheduling(
                    new \DateTime('01.01.2024 12:00'),
                    null,
                    false,
                    true,
                    false,
                    null,
                ),
            ],
            $this->schedulingManager->getAllOccurrencesOfScheduling($scheduling),
        );
    }

    public function testGetOccurrencesSplitMultiday(): void
    {
        // multiday, every monday, 2 times
        $scheduling = new Scheduling(
            new \DateTime('01.01.2024 12:00'),
            new \DateTime('03.01.2024 22:00'),
            false,
            true,
            true,
            'FREQ=WEEKLY;INTERVAL=1;BYDAY=MO;COUNT=2',
        );
        $this->assertEquals(
            [
                new Scheduling(
                    new \DateTime('01.01.2024 12:00'),
                    new \DateTime('01.01.2024 23:59:59.999999'),
                    false,
                    true,
                    true,
                    null,
                ),
                new Scheduling(
                    new \DateTime('02.01.2024 00:00'),
                    new \DateTime('02.01.2024 23:59:59.999999'),
                    true,
                    true,
                    true,
                    null,
                ),
                new Scheduling(
                    new \DateTime('03.01.2024 00:00'),
                    new \DateTime('03.01.2024 22:00'),
                    false,
                    true,
                    true,
                    null,
                ),
                new Scheduling(
                    new \DateTime('08.01.2024 12:00'),
                    new \DateTime('08.01.2024 23:59:59.999999'),
                    false,
                    true,
                    true,
                    null,
                ),
                new Scheduling(
                    new \DateTime('09.01.2024 00:00'),
                    new \DateTime('09.01.2024 23:59:59.999999'),
                    true,
                    true,
                    true,
                    null,
                ),
                new Scheduling(
                    new \DateTime('10.01.2024 00:00'),
                    new \DateTime('10.01.2024 22:00'),
                    false,
                    true,
                    true,
                    null,
                ),
            ],
            $this->schedulingManager->getAllOccurrencesOfScheduling($scheduling, true),
        );
    }


    public function testGetOccurrencesFromTo(): void
    {
        // multiday, every monday
        $scheduling = new Scheduling(
            new \DateTime('01.01.2024 12:00'),
            new \DateTime('03.01.2024 22:00'),
            false,
            true,
            true,
            'FREQ=WEEKLY;INTERVAL=1;BYDAY=MO',
        );

        $from = new \DateTime('02.01.2024');
        $to = new \DateTime('16.01.2024');
        $this->assertEquals(
            [
                new Scheduling(
                    new \DateTime('08.01.2024 12:00'),
                    new \DateTime('10.01.2024 22:00'),
                    false,
                    true,
                    true,
                    null,
                ),
                new Scheduling(
                    new \DateTime('15.01.2024 12:00'),
                    new \DateTime('17.01.2024 22:00'),
                    false,
                    true,
                    true,
                    null,
                ),
            ],
            $this->schedulingManager->getAllOccurrencesOfScheduling($scheduling, false, $from, $to),
        );
        $to = new \DateTime('08.01.2024 23:59:59.999999');
        $this->assertEquals(
            [
                new Scheduling(
                    new \DateTime('08.01.2024 12:00'),
                    new \DateTime('08.01.2024 23:59:59.999999'),
                    false,
                    true,
                    true,
                    null,
                ),
            ],
            $this->schedulingManager->getAllOccurrencesOfScheduling($scheduling, true, $from, $to),
        );
        $to = new \DateTime('09.01.2024 23:59:59.999999');
        $this->assertEquals(
            [
                new Scheduling(
                    new \DateTime('08.01.2024 12:00'),
                    new \DateTime('08.01.2024 23:59:59.999999'),
                    false,
                    true,
                    true,
                    null,
                ),
                new Scheduling(
                    new \DateTime('09.01.2024 00:00'),
                    new \DateTime('09.01.2024 23:59:59.999999'),
                    true,
                    true,
                    true,
                    null,
                ),
            ],
            $this->schedulingManager->getAllOccurrencesOfScheduling($scheduling, true, $from, $to),
        );
    }

    public function testGetOccurrencesLimit(): void
    {
        // multiday, every monday
        $scheduling = new Scheduling(
            new \DateTime('01.01.2024 12:00'),
            new \DateTime('02.01.2024 22:00'),
            false,
            true,
            true,
            'FREQ=WEEKLY;INTERVAL=1;BYDAY=MO',
        );
        $limit = 2;
        $this->assertEquals(
            [
                new Scheduling(
                    new \DateTime('01.01.2024 12:00'),
                    new \DateTime('02.01.2024 22:00'),
                    false,
                    true,
                    true,
                    null,
                ),
                new Scheduling(
                    new \DateTime('08.01.2024 12:00'),
                    new \DateTime('09.01.2024 22:00'),
                    false,
                    true,
                    true,
                    null,
                ),
            ],
            $this->schedulingManager->getAllOccurrencesOfScheduling($scheduling, false, null, null, $limit),
        );
        $this->assertEquals(
            [
                new Scheduling(
                    new \DateTime('01.01.2024 12:00'),
                    new \DateTime('01.01.2024 23:59:59.999999'),
                    false,
                    true,
                    true,
                    null,
                ),
                new Scheduling(
                    new \DateTime('02.01.2024 00:00'),
                    new \DateTime('02.01.2024 22:00'),
                    false,
                    true,
                    true,
                    null,
                ),
            ],
            $this->schedulingManager->getAllOccurrencesOfScheduling($scheduling, true, null, null, $limit),
        );
    }

    public function testGetOccurrencesInfinite(): void
    {
        $scheduling = new Scheduling(
            new \DateTime('01.01.2024 12:00'),
            new \DateTime('01.01.2024 22:00'),
            false,
            true,
            true,
            'FREQ=WEEKLY;INTERVAL=1;BYDAY=MO',
        );
        $this->expectException(\LogicException::class);
        $this->schedulingManager->getAllOccurrencesOfScheduling($scheduling);
    }


    public function testIsMultiDay(): void
    {
        $singleDayScheduling = new Scheduling(
            new \DateTime('2024-01-01'),
            new \DateTime('2024-01-01'),
            false,
            false,
            false,
            null,
        );
        $multiDayScheduling = new Scheduling(
            new \DateTime('2024-01-01'),
            new \DateTime('2024-01-03'),
            false,
            false,
            false,
            null,
        );
        $withoutEndScheduling = new Scheduling(
            new \DateTime('2024-01-01'),
            null,
            false,
            false,
            false,
            null,
        );
        $this->assertFalse($this->schedulingManager->isMultiDay($singleDayScheduling));
        $this->assertTrue($this->schedulingManager->isMultiDay($multiDayScheduling));
        $this->assertNull($this->schedulingManager->isMultiDay($withoutEndScheduling));
    }

    public function testIsInfinite(): void
    {
        $finiteScheduling = new Scheduling(
            new \DateTime('2024-01-01'),
            null,
            false,
            false,
            false,
            'FREQ=DAILY;INTERVAL=1;COUNT=1',
        );
        $infiniteScheduling = new Scheduling(
            new \DateTime('2024-01-01'),
            null,
            false,
            false,
            false,
            'FREQ=DAILY;INTERVAL=1',
        );
        $this->assertFalse($this->schedulingManager->isInfinite($finiteScheduling));
        $this->assertTrue($this->schedulingManager->isInfinite($infiniteScheduling));
    }

    public function testGetOccurrencesMultipleSchedulesEmpty(): void
    {
        $this->assertEmpty(
            $this->schedulingManager->getAllOccurrencesOfSchedulings([]),
        );
    }

    public function testGetOccurrencesMultipleSchedulesSingle(): void
    {
        $scheduling = new Scheduling(
            new \DateTime('01.01.2024 12:00'),
            new \DateTime('02.01.2024 22:00'),
            false,
            true,
            true,
            'FREQ=WEEKLY;INTERVAL=1;BYDAY=MO;COUNT=3',
        );
        $this->assertEquals(
            $this->schedulingManager->getAllOccurrencesOfScheduling($scheduling),
            $this->schedulingManager->getAllOccurrencesOfSchedulings([$scheduling]),
        );
    }

    public function testGetOccurrencesMultipleSchedulesMulti(): void
    {
        $schedulings = [
            new Scheduling(
                new \DateTime('01.01.2024 12:00'),
                new \DateTime('02.01.2024 22:00'),
                false,
                true,
                true,
                'FREQ=WEEKLY;INTERVAL=1;BYDAY=MO;COUNT=2',
            ),
            new Scheduling(
                new \DateTime('03.01.2024 14:00'),
                new \DateTime('03.01.2024 20:00'),
                false,
                true,
                true,
                'FREQ=WEEKLY;INTERVAL=1;BYDAY=TH;COUNT=2',
            ),
        ];
        $this->assertEquals(
            $this->schedulingManager->getAllOccurrencesOfSchedulings(
                $schedulings,
                true,
            ),
            [
                new Scheduling(
                    new \DateTime('01.01.2024 12:00'),
                    new \DateTime('01.01.2024 23:59:59.999999'),
                    false,
                    true,
                    true,
                    null,
                ),
                new Scheduling(
                    new \DateTime('02.01.2024 00:00'),
                    new \DateTime('02.01.2024 22:00'),
                    false,
                    true,
                    true,
                    null,
                ),
                new Scheduling(
                    new \DateTime('04.01.2024 14:00'),
                    new \DateTime('04.01.2024 20:00'),
                    false,
                    true,
                    true,
                    null,
                ),
                new Scheduling(
                    new \DateTime('08.01.2024 12:00'),
                    new \DateTime('08.01.2024 23:59:59.999999'),
                    false,
                    true,
                    true,
                    null,
                ),
                new Scheduling(
                    new \DateTime('09.01.2024 00:00'),
                    new \DateTime('09.01.2024 22:00'),
                    false,
                    true,
                    true,
                    null,
                ),
                new Scheduling(
                    new \DateTime('11.01.2024 14:00'),
                    new \DateTime('11.01.2024 20:00'),
                    false,
                    true,
                    true,
                    null,
                ),
            ],
        );
    }

    public function testGetOccurrencesMultipleSchedulesWithInfinite(): void
    {
        $schedulings = [
            new Scheduling(
                new \DateTime('01.01.2024 12:00'),
                new \DateTime('01.01.2024 22:00'),
                false,
                true,
                true,
                null,
            ),
            new Scheduling(
                new \DateTime('01.02.2024 12:00'),
                new \DateTime('01.02.2024 22:00'),
                false,
                true,
                true,
                'FREQ=WEEKLY;INTERVAL=1',
            ),
        ];
        $this->expectException(\LogicException::class);
        $this->schedulingManager->getAllOccurrencesOfSchedulings($schedulings);
    }

    public function testFindOccurrenceSingleScheduling(): void
    {
        $scheduling = new Scheduling(
            new \DateTime('02.02.2025 12:00'),
            new \DateTime('02.02.2025 22:00'),
            false,
            true,
            true,
            'FREQ=WEEKLY;INTERVAL=1;COUNT=2',
        );
        $this->assertEquals(
            new Scheduling(
                new \DateTime('09.02.2025 12:00'),
                new \DateTime('09.02.2025 22:00'),
                false,
                true,
                true,
                null,
            ),
            $this->schedulingManager->findOccurrenceOfScheduling($scheduling, new \DateTime('09.02.2025 12:00')),
        );
    }

    public function testFindOccurrenceSingleSchedulingNotFound(): void
    {
        $scheduling = new Scheduling(
            new \DateTime('02.02.2025 12:00'),
            new \DateTime('02.02.2025 22:00'),
            false,
            true,
            true,
            'FREQ=WEEKLY;INTERVAL=1;COUNT=2',
        );
        $this->assertNull(
            $this->schedulingManager->findOccurrenceOfScheduling($scheduling, new \DateTime('06.02.2025 12:00')),
        );
    }

    public function testFindOccurrenceMultipleSchedulings(): void
    {
        $schedulings = [
            new Scheduling(
                new \DateTime('02.02.2025 12:00'),
                new \DateTime('02.02.2025 22:00'),
                false,
                true,
                true,
                'FREQ=WEEKLY;INTERVAL=1;COUNT=2',
            ),
            new Scheduling(
                new \DateTime('17.03.2025 14:00'),
                new \DateTime('17.03.2025 20:00'),
                false,
                true,
                true,
                'FREQ=DAILY;INTERVAL=1;COUNT=10',
            ),
        ];
        $this->assertEquals(
            new Scheduling(
                new \DateTime('20.03.2025 14:00'),
                new \DateTime('20.03.2025 20:00'),
                false,
                true,
                true,
                null,
            ),
            $this->schedulingManager->findOccurrenceOfSchedulings($schedulings, new \DateTime('20.03.2025 14:00')),
        );
    }

    public function testFindOccurrenceMultipleSchedulingsNotFound(): void
    {
        $schedulings = [
            new Scheduling(
                new \DateTime('02.02.2025 12:00'),
                new \DateTime('02.02.2025 22:00'),
                false,
                true,
                true,
                'FREQ=WEEKLY;INTERVAL=1;COUNT=2',
            ),
            new Scheduling(
                new \DateTime('17.03.2025 14:00'),
                new \DateTime('17.03.2025 20:00'),
                false,
                true,
                true,
                'FREQ=DAILY;INTERVAL=1;COUNT=10',
            ),
        ];
        $this->assertNull(
            $this->schedulingManager->findOccurrenceOfSchedulings($schedulings, new \DateTime('04.10.2024 12:00')),
        );
    }
}
