<?php

declare(strict_types=1);

namespace Atoolo\EventsCalendar\Test\Service\GraphQL\Factory;

use Atoolo\EventsCalendar\Service\GraphQL\Factory\SchedulingFactory;
use Atoolo\EventsCalendar\Test\Constraint\EqualsRRule;
use Atoolo\EventsCalendar\Test\Constraint\IsRRule;
use Atoolo\Resource\DataBag;
use Atoolo\Resource\Resource;
use Atoolo\Resource\ResourceLanguage;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SchedulingFactory::class)]
class SchedulingFactoryTest extends TestCase
{
    private SchedulingFactory $factory;

    // 01.01.2025 00:00 GMT
    private const UNTIL = 1735689600;

    public function setUp(): void
    {
        $this->factory = new SchedulingFactory();
    }

    public function testCreate()
    {
        $resource = $this->createResource([
            'metadata' => [
                'schedulingRaw' => [
                    [
                        "beginDate" => 1640991600,
                        "beginTime" => "19:58",
                    ],
                ],
            ],
        ]);
        $scheduling = $this->factory->create($resource);
        $this->assertEquals(
            1641063480,
            $scheduling[0]->start->getTimestamp(),
        );
    }

    public function testCreateFromRawSchedulungInvalid()
    {
        $rawScheduling = [
            "type" => "single",
            "isFullDay" => true,
            "endDate" => 1726178400,
        ];
        $scheduling = $this->factory->createFromRawSchedulung(
            $rawScheduling,
        );
        $this->assertNull($scheduling);
    }

    public function testCreateFromRawSchedulungMulti()
    {
        $rawScheduling = [
            "type" => "multi",
            "isFullDay" => true,
            "beginDate" => 1726005600,
            "endDate" => 1726178400,
        ];
        $scheduling = $this->factory->createFromRawSchedulung(
            $rawScheduling,
        );
        $this->assertEquals(1726005600, $scheduling->start->getTimestamp());
        $this->assertEquals(1726178400, $scheduling->end->getTimestamp());
        $this->assertTrue($scheduling->isFullDay);
        $this->assertFalse($scheduling->hasStartTime);
        $this->assertFalse($scheduling->hasEndTime);
    }

    public function testGetStartDateTimeFromRawScheduling()
    {
        $rawScheduling = [
            "beginDate" => 1640991600,
            "beginTime" => "19:58",
        ];
        $startDateTime = $this->factory->getStartDateTimeFromRawScheduling(
            $rawScheduling,
        );
        $this->assertEquals(
            1641063480,
            $startDateTime->getTimestamp(),
        );
    }

    public function testGetEndDateTimeFromRawScheduling()
    {
        $rawScheduling = [
            "endDate" => 1640991600,
            "endTime" => "invalid:time",
        ];
        $endDateTime = $this->factory->getEndDateTimeFromRawScheduling(
            $rawScheduling,
        );
        $this->assertEquals(
            1640991600,
            $endDateTime->getTimestamp(),
        );
    }

    public function testGetEndDateTimeFromRawSchedulingNull()
    {
        $rawScheduling = [];
        $endDateTime = $this->factory->getEndDateTimeFromRawScheduling(
            $rawScheduling,
        );
        $this->assertNull($endDateTime);
    }

    public function testGetEndDateTimeFromRawSchedulingInvalid()
    {
        $rawScheduling = [
            "endDate" => '1640991600',
            "endTime" => "invalid:time",
        ];
        $endDateTime = $this->factory->getEndDateTimeFromRawScheduling(
            $rawScheduling,
        );
        $this->assertNull($endDateTime);
    }

    public function testGetRRuleFromRawSchedulingEmtpy()
    {
        $this->assertNull(
            $this->factory->getRRuleFromRawScheduling([
                "type" => "single",
                "isFullDay" => false,
                "beginDate" => 1725487200,
                "beginTime" => "11:00",
                "endTime" => "12:00",
            ]),
        );
    }

    /**
     * Alle 6 Tage zum bis 01.01.2025 00:00
     */
    public function testGetRRuleFromRawSchedulingDailyUntil()
    {
        $actual = $this->factory->getRRuleFromRawScheduling([
            "type" => "daily",
            "repetition" => [
                "date" => self::UNTIL,
                "interval" => 6,
            ],
        ]);
        $expected = 'FREQ=DAILY;INTERVAL=6;UNTIL=20250101T000000Z';
        $this->assertThat($actual, new IsRRule());
        $this->assertThat($actual, new EqualsRRule($expected));
    }

    /**
     * Alle 2 Tage , bis 3 Wiederholungen
     */
    public function testGetRRuleFromRawSchedulingDailyCount()
    {
        $actual = $this->factory->getRRuleFromRawScheduling([
            "type" => "daily",
            "repetition" => [
                "count" => 3,
                "interval" => 2,
            ],
        ]);
        $expected = 'FREQ=DAILY;INTERVAL=2;COUNT=3';
        $this->assertThat($actual, new IsRRule());
        $this->assertThat($actual, new EqualsRRule($expected));
    }

    /**
     * Alle 3 Wochen, Mo, Mi, Do, bis 01.01.2025 00:00
     */
    public function testGetRRuleFromRawSchedulingWeeklyUntil()
    {
        $actual = $this->factory->getRRuleFromRawScheduling([
            "type" => "weekly",
            "repetition" => [
                "date" => self::UNTIL,
                "dow" => "mon,wed,thu",
                "interval" => 3,
            ],
        ]);
        $expected = 'FREQ=WEEKLY;INTERVAL=3;BYDAY=MO,WE,TH;UNTIL=20250101T000000Z';
        $this->assertThat($actual, new IsRRule());
        $this->assertThat($actual, new EqualsRRule($expected));
    }

    /**
     * Alle 4 Wochen, Di, Fr, Sa, So, bis 7 Wiederholungen
     */
    public function testGetRRuleFromRawSchedulingWeeklyCount()
    {
        $actual = $this->factory->getRRuleFromRawScheduling([
            "type" => "weekly",
            "repetition" => [
                "count" => 7,
                "dow" => "tue,fri,sat,sun",
                "interval" => 4,
            ],
        ]);
        $expected = 'FREQ=WEEKLY;INTERVAL=4;BYDAY=SU,TU,FR,SA;COUNT=7';
        $this->assertThat($actual, new IsRRule());
        $this->assertThat($actual, new EqualsRRule($expected));
    }

    /**
     * Alle 3 Monate, jeden 9. Tag, bis 01.01.2025 00:00
     */
    public function testGetRRuleFromRawSchedulingMonthlyByDayUntil()
    {
        $actual = $this->factory->getRRuleFromRawScheduling([
            "type" => "monthlyByDay",
            "repetition" => [
                "date" => self::UNTIL,
                "dom" => 9,
                "interval" => 3,
            ],
        ]);
        $expected = 'FREQ=MONTHLY;INTERVAL=3;BYMONTHDAY=9;UNTIL=20250101T000000Z';
        $this->assertThat($actual, new IsRRule());
        $this->assertThat($actual, new EqualsRRule($expected));
    }

    /**
     * Alle 6 Monate, jeden 2. Freitag, bis 01.01.2025 00:00
     */
    public function testGetRRuleFromRawSchedulingMonthlyByOccurrenceUntil()
    {
        $actual = $this->factory->getRRuleFromRawScheduling([
            "type" => "monthlyByOccurrence",
            "repetition" => [
                "date" => self::UNTIL,
                "oom" => 2,
                "dow" => "fri",
                "interval" => 6,
            ],
        ]);
        $expected = 'FREQ=MONTHLY;INTERVAL=6;BYDAY=2FR;UNTIL=20250101T000000Z';
        $this->assertThat($actual, new IsRRule());
        $this->assertThat($actual, new EqualsRRule($expected));
    }

    /**
     * Jedes Jahr, jeden 6. April, bis 01.01.2025 00:00
     */
    public function testGetRRuleFromRawSchedulingYearlyByMonthUntil()
    {
        $actual = $this->factory->getRRuleFromRawScheduling([
            "type" => "yearlyByMonth",
            "repetition" => [
                "date" => self::UNTIL,
                "dom" => 6,
                "moy" => 3,
                "interval" => 1,
            ],
        ]);
        $expected = 'FREQ=YEARLY;INTERVAL=1;BYMONTH=4;BYMONTHDAY=6;UNTIL=20250101T000000Z';
        $this->assertThat($actual, new IsRRule());
        $this->assertThat($actual, new EqualsRRule($expected));
    }

    /**
     * Jedes 2. Jahr, jeden 3. Sonntag im März, bis 01.01.2025 00:00
     */
    public function testGetRRuleFromRawSchedulingYearlyByOccurrenceUntil()
    {
        $actual = $this->factory->getRRuleFromRawScheduling([
            "type" => "yearlyByOccurrence",
            "repetition" => [
                "date" => self::UNTIL,
                "oom" => 3,
                "dow" => "sun",
                "moy" => 2,
                "interval" => 2,
            ],
        ]);
        $expected = 'FREQ=YEARLY;INTERVAL=2;BYMONTH=3;BYDAY=3SU;UNTIL=20250101T000000Z';
        $this->assertThat($actual, new IsRRule());
        $this->assertThat($actual, new EqualsRRule($expected));
    }

    /**
     * @param array<string,mixed> $data
     */
    private function createResource(array $data): Resource
    {
        return new Resource(
            $data['url'] ?? '',
            $data['id'] ?? '',
            $data['name'] ?? '',
            $data['objectType'] ?? '',
            ResourceLanguage::default(),
            new DataBag($data),
        );
    }
}
