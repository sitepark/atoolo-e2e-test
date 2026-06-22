<?php

declare(strict_types=1);

namespace Atoolo\EventsCalendar\Test\Service\ICal;

use Atoolo\EventsCalendar\Dto\Scheduling\Scheduling;
use Atoolo\EventsCalendar\Service\GraphQL\Factory\SchedulingFactory;
use Atoolo\EventsCalendar\Service\ICal\ICalFactory;
use Atoolo\Resource\DataBag;
use Atoolo\Resource\Resource;
use Atoolo\Resource\ResourceChannel;
use Atoolo\Resource\ResourceLanguage;
use Atoolo\Resource\ResourceTenant;
use DateTimeZone;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

#[CoversClass(ICalFactory::class)]
class ICalFactoryTest extends TestCase
{
    private SchedulingFactory&MockObject $schedulingFactory;

    private ResourceChannel $resourceChannel;

    private ICalFactory $iCalFactory;

    public function setUp(): void
    {
        $this->schedulingFactory = $this->createMock(
            SchedulingFactory::class,
        );
        $this->resourceChannel = $this->createResourceChannel([
            'serverName' => 'www.test.de',
        ]);
        $this->iCalFactory = new ICalFactory(
            $this->schedulingFactory,
            $this->resourceChannel,
        );
    }

    public function testCreateCalendarAsString(): void
    {
        $resource = $this->createResource([
            'id' => '1',
            'url' => '/some/location',
            'metadata' => [
                'headline' => 'Event',
                'description' => 'Amazing event',
            ],
        ]);
        $this->schedulingFactory
            ->method('create')
            ->with($resource)
            ->willReturn([
                // every monday, 3 times
                new Scheduling(
                    new \DateTime('01.01.2024 12:00', new DateTimeZone('Europe/Berlin')),
                    new \DateTime('01.01.2024 22:30', new DateTimeZone('Europe/Berlin')),
                    false,
                    true,
                    true,
                    'FREQ=WEEKLY;INTERVAL=1;BYDAY=MO;COUNT=3',
                ),
                new Scheduling(
                    new \DateTime('01.02.2024', new DateTimeZone('Europe/Berlin')),
                    new \DateTime('04.02.2024', new DateTimeZone('Europe/Berlin')),
                    true,
                    false,
                    false,
                    null,
                ),
                new Scheduling(
                    new \DateTime('11.02.2024', new DateTimeZone('Europe/Berlin')),
                    new \DateTime('14.02.2024', new DateTimeZone('Europe/Berlin')),
                    false,
                    false,
                    false,
                    null,
                ),
            ]);
        $result = $this->iCalFactory->createCalendarFromResourcesAsString([$resource]);
        $resultLines = array_values(
            array_filter(
                explode("\r\n", $result),
                function ($row) {
                    return !str_starts_with($row, 'DTSTAMP'); // Ignore timestamp
                },
            ),
        );
        $expectedLines = [
            'BEGIN:VCALENDAR',
            'PRODID:-//atoolo/events-calendar-bundle//1.0/EN',
            'VERSION:2.0',
            'CALSCALE:GREGORIAN',
            'BEGIN:VEVENT',
            'UID:1-0@www.test.de',
            // 'DTSTAMP:20241212T131535Z', Ignore timestamp
            'SUMMARY:Event',
            'DESCRIPTION:Amazing event',
            'URL:https://www.test.de/some/location',
            'DTSTART;TZID=Europe/Berlin:20240101T120000',
            'DTEND;TZID=Europe/Berlin:20240101T223000',
            'RRULE:FREQ=WEEKLY;INTERVAL=1;BYDAY=MO;COUNT=3',
            'END:VEVENT',
            'BEGIN:VEVENT',
            'UID:1-1@www.test.de',
            // 'DTSTAMP:20241212T131535Z', Ignore timestamp
            'SUMMARY:Event',
            'DESCRIPTION:Amazing event',
            'URL:https://www.test.de/some/location',
            'DTSTART;VALUE=DATE:20240201',
            'DTEND;VALUE=DATE:20240205',
            'RELATED-TO:1-0@www.test.de',
            'END:VEVENT',
            'BEGIN:VEVENT',
            'UID:1-2@www.test.de',
            // 'DTSTAMP:20241212T131535Z', Ignore timestamp
            'SUMMARY:Event',
            'DESCRIPTION:Amazing event',
            'URL:https://www.test.de/some/location',
            'DTSTART;TZID=Europe/Berlin:20240211T000000',
            'DTEND;TZID=Europe/Berlin:20240214T235959',
            'RELATED-TO:1-0@www.test.de',
            'END:VEVENT',
            'END:VCALENDAR',
            '',
        ];
        $this->assertEquals($expectedLines, $resultLines);
    }

    public function testCreateCalendarAsStringAtOccurrence(): void
    {
        $resource = $this->createResource([
            'id' => '1',
            'url' => '/some/location',
            'metadata' => [
                'headline' => 'Event',
                'description' => 'Amazing event',
            ],
        ]);
        $this->schedulingFactory
            ->method('create')
            ->with($resource)
            ->willReturn([
                // every monday, 3 times
                new Scheduling(
                    new \DateTime('01.01.2024 12:00', new DateTimeZone('Europe/Berlin')),
                    new \DateTime('01.01.2024 22:30', new DateTimeZone('Europe/Berlin')),
                    false,
                    true,
                    true,
                    'FREQ=WEEKLY;INTERVAL=1;BYDAY=MO;COUNT=3',
                ),
                new Scheduling(
                    new \DateTime('01.02.2024', new DateTimeZone('Europe/Berlin')),
                    new \DateTime('04.02.2024', new DateTimeZone('Europe/Berlin')),
                    true,
                    false,
                    false,
                    null,
                ),
                new Scheduling(
                    new \DateTime('11.02.2024', new DateTimeZone('Europe/Berlin')),
                    new \DateTime('14.02.2024', new DateTimeZone('Europe/Berlin')),
                    false,
                    false,
                    false,
                    null,
                ),
            ]);
        $result = $this->iCalFactory->createCalendarFromResourcesAsString(
            [$resource],
            new \DateTime('08.01.2024 12:00', new DateTimeZone('Europe/Berlin')),
        );
        $resultLines = array_values(
            array_filter(
                explode("\r\n", $result),
                function ($row) {
                    return !str_starts_with($row, 'DTSTAMP'); // Ignore timestamp
                },
            ),
        );
        $expectedLines = [
            'BEGIN:VCALENDAR',
            'PRODID:-//atoolo/events-calendar-bundle//1.0/EN',
            'VERSION:2.0',
            'CALSCALE:GREGORIAN',
            'BEGIN:VEVENT',
            'UID:1-0@www.test.de',
            // 'DTSTAMP:20241212T131535Z', Ignore timestamp
            'SUMMARY:Event',
            'DESCRIPTION:Amazing event',
            'URL:https://www.test.de/some/location',
            'DTSTART;TZID=Europe/Berlin:20240108T120000',
            'DTEND;TZID=Europe/Berlin:20240108T223000',
            'END:VEVENT',
            'END:VCALENDAR',
            '',
        ];
        $this->assertEquals($expectedLines, $resultLines);
    }


    public function testCreateCalendarAsStringExternal(): void
    {
        $resource = $this->createResource([
            'id' => '1',
            'url' => 'https://www.external.de/some/location',
            'metadata' => [
                'headline' => 'Event',
                'description' => 'Amazing event',
            ],
        ]);
        $this->schedulingFactory
            ->method('create')
            ->with($resource)
            ->willReturn([
                new Scheduling(
                    new \DateTime('09.02.2024', new DateTimeZone('Europe/Berlin')),
                    null,
                    true,
                    false,
                    false,
                    null,
                ),
            ]);
        $result = $this->iCalFactory->createCalendarFromResourcesAsString([$resource]);
        $resultLines = array_values(
            array_filter(
                explode("\r\n", $result),
                function ($row) {
                    return !str_starts_with($row, 'DTSTAMP'); // Ignore timestamp
                },
            ),
        );
        $expectedLines = [
            'BEGIN:VCALENDAR',
            'PRODID:-//atoolo/events-calendar-bundle//1.0/EN',
            'VERSION:2.0',
            'CALSCALE:GREGORIAN',
            'BEGIN:VEVENT',
            'UID:1-0@www.test.de',
            // 'DTSTAMP:20241212T131535Z', Ignore timestamp
            'SUMMARY:Event',
            'DESCRIPTION:Amazing event',
            'URL:https://www.external.de/some/location',
            'DTSTART;VALUE=DATE:20240209',
            'END:VEVENT',
            'END:VCALENDAR',
            '',
        ];
        $this->assertEquals($expectedLines, $resultLines);
    }

    private function createResourceChannel(array $args): ResourceChannel
    {
        /** @var ResourceTenant $tenant */
        $tenant = $this->createStub(ResourceTenant::class);
        return new ResourceChannel(
            id: $args['id'] ?? '',
            name: $args['name'] ?? '',
            anchor: $args['anchor'] ?? '',
            serverName: $args['serverName'] ?? '',
            isPreview: $args['isPreview'] ?? false,
            nature: $args['nature'] ?? '',
            locale: $args['locale'] ?? '',
            baseDir: $args['baseDir'] ?? '',
            resourceDir: $args['resourceDir'] ?? '',
            configDir: $args['configDir'] ?? '',
            searchIndex: $args['searchIndex'] ?? '',
            translationLocales: $args['translationLocales'] ?? [],
            attributes: new DataBag([]),
            tenant: $tenant,
        );
    }

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
