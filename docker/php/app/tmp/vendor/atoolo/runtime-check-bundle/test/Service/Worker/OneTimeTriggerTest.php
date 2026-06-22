<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Check\Test\Service\Worker;

use Atoolo\Runtime\Check\Service\Worker\OneTimeTrigger;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(OneTimeTrigger::class)]
class OneTimeTriggerTest extends TestCase
{
    public function testToString(): void
    {
        $trigger = new OneTimeTrigger();

        $this->assertEquals(
            'one time',
            (string) $trigger,
        );
    }

    public function testToStringAlreadyRunning(): void
    {
        $trigger = new OneTimeTrigger();
        $trigger->getNextRunDate(new \DateTimeImmutable());

        $this->assertEquals(
            'one time (already running)',
            (string) $trigger,
        );
    }

    public function testGetNextRunDate(): void
    {
        $trigger = new OneTimeTrigger();

        $run = new \DateTimeImmutable();
        $this->assertEquals(
            $run,
            $trigger->getNextRunDate($run),
            'The first call to getNextRunDate should return the same date',
        );
    }

    public function testSecondGetNextRunDate(): void
    {
        $trigger = new OneTimeTrigger();
        $run = new \DateTimeImmutable();
        $trigger->getNextRunDate($run);

        $this->assertNull(
            $trigger->getNextRunDate($run),
            'The second call to getNextRunDate should return null',
        );
    }
}
