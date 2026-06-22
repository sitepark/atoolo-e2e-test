<?php

declare(strict_types=1);

namespace Atoolo\EventsCalendar\Test\Dto\Scheduling;

use Atoolo\EventsCalendar\Dto\Scheduling\Scheduling;
use Atoolo\EventsCalendar\Dto\Scheduling\SchedulingBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SchedulingBuilder::class)]
class SchedulingBuilderTest extends TestCase
{
    public function testFromScheduling(): void
    {
        $scheduling = new Scheduling(
            new \DateTime('01.01.2024 12:00'),
            new \DateTime('02.01.2024'),
            false,
            true,
            false,
            'FREQ=WEEKLY;INTERVAL=1;BYDAY=MO;COUNT=3',
        );
        $this->assertEquals(
            $scheduling,
            (new SchedulingBuilder())
                ->fromScheduling($scheduling)
                ->build(),
        );
    }

    public function testSetters(): void
    {
        $this->assertEquals(
            new Scheduling(
                new \DateTime('01.01.2024 12:00'),
                new \DateTime('02.01.2024'),
                false,
                true,
                false,
                'FREQ=WEEKLY;INTERVAL=1;BYDAY=MO;COUNT=3',
            ),
            (new SchedulingBuilder())
                ->setStart(new \DateTime('01.01.2024 12:00'))
                ->setEnd(new \DateTime('02.01.2024'))
                ->setIsFullDay(false)
                ->setHasStartTime(true)
                ->setHasEndTime(false)
                ->setRRule('FREQ=WEEKLY;INTERVAL=1;BYDAY=MO;COUNT=3')
                ->build(),
        );
    }

    public function testSetStartKeepRelative(): void
    {
        $oldScheduling =  new Scheduling(
            new \DateTime('01.01.2024 12:00'),
            new \DateTime('02.01.2024 15:00'),
            false,
            true,
            true,
            null,
        );
        $this->assertEquals(
            new Scheduling(
                new \DateTime('01.02.2024 12:00'),
                new \DateTime('02.02.2024 15:00'),
                false,
                true,
                true,
                null,
            ),
            (new SchedulingBuilder())
                ->fromScheduling($oldScheduling)
                ->setStart(new \DateTime('01.02.2024 12:00'), true)
                ->build(),
        );
    }
}
