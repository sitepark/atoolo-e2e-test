<?php

declare(strict_types=1);

namespace Atoolo\EventsCalendar\Test\Service\RceEvent;

use Atoolo\EventsCalendar\Dto\RceEvent\RceEventAddress;
use Atoolo\EventsCalendar\Dto\RceEvent\RceEventAddresses;
use Atoolo\EventsCalendar\Dto\RceEvent\RceEventDate;
use Atoolo\EventsCalendar\Dto\RceEvent\RceEventListItem;
use Atoolo\EventsCalendar\Dto\RceEvent\RceEventSource;
use Atoolo\EventsCalendar\Dto\RceEvent\RceEventTheme;
use Atoolo\EventsCalendar\Dto\RceEvent\RceEventUpload;
use Atoolo\EventsCalendar\Service\RceEvent\RceEventListItemFactory;
use DateTime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

#[CoversClass(RceEventListItemFactory::class)]
class RceEventListItemFactoryTest extends TestCase
{
    private RceEventListItemFactory $factory;

    private LoggerInterface|MockObject $logger;

    public function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->factory = new RceEventListItemFactory($this->logger);
    }

    public function testDefaults(): void
    {
        $event = $this->create(
            '<EVENT id="659.1227097009"><DATAPOOL id="1"/></EVENT>',
        );
        $expected = new RceEventListItem(
            '1-659.1227097009',
            '',
            false,
            [],
            '',
            false,
            false,
            '',
            null,
            null,
            false,
            null,
            new RceEventAddresses(),
            '',
            [],
        );

        $this->assertEquals($expected, $event, 'unexpected defaults');
    }

    public function testIsActive(): void
    {
        $event = $this->create('<EVENT active="yes"></EVENT>');
        $this->assertTrue($event->active, 'unexpected active');
    }

    public function testIsActiveWithNo(): void
    {
        $event = $this->create('<EVENT active="no"></EVENT>');
        $this->assertFalse($event->active, 'unexpected active');
    }

    public function testIsActiveWithoutAttribute(): void
    {
        $event = $this->create('<EVENT></EVENT>');
        $this->assertFalse($event->active, 'unexpected active');
    }

    public function testDateList(): void
    {
        $event = $this->create(
            <<<EOS
            <EVENT>
                <DATELIST>
                    <DATE hashid="0486b53176b3e98ba3c6e2175f02275d">
                        <STARTDATE>2024-03-08</STARTDATE>
                        <STARTTIME>17:30:00</STARTTIME>
                        <ENDTIME>19:00:00</ENDTIME>
                    </DATE>
                </DATELIST>
            </EVENT>
            EOS,
        );

        $startDate = new DateTime();
        $startDate->setDate(2024, 3, 8);
        $startDate->setTime(17, 30);
        $endDate = new DateTime();
        $endDate->setDate(2024, 3, 8);
        $endDate->setTime(19, 0);

        $expectedDate = new RceEventDate(
            '0486b53176b3e98ba3c6e2175f02275d',
            $startDate,
            $endDate,
            false,
            false,
            false,
            postponed: false,
        );
        $this->assertEquals(
            [$expectedDate],
            $event->dates,
            'unexpected dateList',
        );
    }

    public function testDateListWithBacklist(): void
    {
        $event = $this->create(
            <<<EOS
            <EVENT>
                <DATELIST>
                    <DATE>
                        <BLACKLISTLIST>
                            <BLACKLIST tplid="1224">yes</BLACKLIST>
                        </BLACKLISTLIST>
                    </DATE>
                </DATELIST>
            </EVENT>
            EOS,
        );

        $empty = new DateTime();
        $empty->setTimestamp(0);

        $expectedDate = new RceEventDate(
            '',
            $empty,
            $empty,
            true,
            false,
            false,
            postponed: false,
        );
        $this->assertEquals(
            [$expectedDate],
            $event->dates,
            'unexpected backlisted',
        );
    }

    public function testDateListWithSoldOut(): void
    {
        $event = $this->create(
            <<<EOS
            <EVENT>
                <DATELIST>
                    <DATE>
                        <STATUS>soldout</STATUS>
                    </DATE>
                </DATELIST>
            </EVENT>
            EOS,
        );

        $empty = new DateTime();
        $empty->setTimestamp(0);

        $expectedDate = new RceEventDate(
            '',
            $empty,
            $empty,
            false,
            true,
            false,
            postponed: false,
        );
        $this->assertEquals(
            [$expectedDate],
            $event->dates,
            'unexpected soldout',
        );
    }

    public function testDateListWithAusverkauft(): void
    {
        $event = $this->create(
            <<<EOS
            <EVENT>
                <DATELIST>
                    <DATE>
                        <STATUS>ausverkauft</STATUS>
                    </DATE>
                </DATELIST>
            </EVENT>
            EOS,
        );

        $empty = new DateTime();
        $empty->setTimestamp(0);

        $expectedDate = new RceEventDate(
            '',
            $empty,
            $empty,
            false,
            true,
            false,
            postponed: false,
        );
        $this->assertEquals(
            [$expectedDate],
            $event->dates,
            'unexpected soldout',
        );
    }


    public function testDateListWithCanceled(): void
    {
        $event = $this->create(
            <<<EOS
            <EVENT>
                <DATELIST>
                    <DATE>
                        <STATUS>canceled</STATUS>
                    </DATE>
                </DATELIST>
            </EVENT>
            EOS,
        );

        $empty = new DateTime();
        $empty->setTimestamp(0);

        $expectedDate = new RceEventDate(
            '',
            $empty,
            $empty,
            false,
            false,
            true,
            postponed: false,
        );
        $this->assertEquals(
            [$expectedDate],
            $event->dates,
            'unexpected canceled',
        );
    }

    public function testDateListWithAbgesagt(): void
    {
        $event = $this->create(
            <<<EOS
            <EVENT>
                <DATELIST>
                    <DATE>
                        <STATUS>abgesagt</STATUS>
                    </DATE>
                </DATELIST>
            </EVENT>
            EOS,
        );

        $empty = new DateTime();
        $empty->setTimestamp(0);

        $expectedDate = new RceEventDate(
            '',
            $empty,
            $empty,
            false,
            false,
            true,
            postponed: false,
        );
        $this->assertEquals(
            [$expectedDate],
            $event->dates,
            'unexpected canceled',
        );
    }

    public function testDateListWithPostponed(): void
    {
        $event = $this->create(
            <<<EOS
            <EVENT>
                <DATELIST>
                    <DATE>
                        <STATUS>postponed</STATUS>
                    </DATE>
                </DATELIST>
            </EVENT>
            EOS,
        );

        $empty = new DateTime();
        $empty->setTimestamp(0);

        $expectedDate = new RceEventDate(
            '',
            $empty,
            $empty,
            false,
            false,
            false,
            true,
        );
        $this->assertEquals(
            [$expectedDate],
            $event->dates,
            'unexpected postponed',
        );
    }
    public function testDateListWithVerschoben(): void
    {
        $event = $this->create(
            <<<EOS
            <EVENT>
                <DATELIST>
                    <DATE>
                        <STATUS>verschoben</STATUS>
                    </DATE>
                </DATELIST>
            </EVENT>
            EOS,
        );

        $empty = new DateTime();
        $empty->setTimestamp(0);

        $expectedDate = new RceEventDate(
            '',
            $empty,
            $empty,
            false,
            false,
            false,
            true,
        );
        $this->assertEquals(
            [$expectedDate],
            $event->dates,
            'unexpected postponed',
        );
    }


    public function testDateListDefaultTimes(): void
    {
        $event = $this->create(
            <<<EOS
            <EVENT>
                <DATELIST>
                    <DATE>
                        <STARTDATE>2024-03-08</STARTDATE>
                    </DATE>
                </DATELIST>
            </EVENT>
            EOS,
        );

        $startDate = new DateTime();
        $startDate->setDate(2024, 3, 8);
        $startDate->setTime(0, 0);
        $endDate = new DateTime();
        $endDate->setDate(2024, 3, 8);
        $endDate->setTime(23, 59, 59);

        $expectedDate = new RceEventDate(
            '',
            $startDate,
            $endDate,
            false,
            false,
            false,
            postponed: false,
        );
        $this->assertEquals(
            [$expectedDate],
            $event->dates,
            'unexpected dateList',
        );
    }

    public function testDateListWithoutStartDate(): void
    {
        $event = $this->create(
            <<<EOS
            <EVENT>
                <DATELIST>
                    <DATE>
                    </DATE>
                </DATELIST>
            </EVENT>
            EOS,
        );

        $empty = new DateTime();
        $empty->setTimestamp(0);


        $expectedDate = new RceEventDate(
            '',
            $empty,
            $empty,
            false,
            false,
            false,
            postponed: false,
        );
        $this->assertEquals(
            [$expectedDate],
            $event->dates,
            'unexpected dateList',
        );
    }

    public function testDateListWithInvalidFormat(): void
    {
        $this->logger->expects($this->exactly(2))->method('error');
        $this->create(
            <<<EOS
            <EVENT>
                <DATELIST>
                    <DATE>
                        <STARTDATE>abc</STARTDATE>
                    </DATE>
                </DATELIST>
            </EVENT>
            EOS,
        );
    }

    public function testDescription(): void
    {
        $event = $this->create(
            <<<EOS
            <EVENT>
                <DESCRIPTION><![CDATA[
                    <p>test</p><br>
                    <p>test2</p>                   
                ]]></DESCRIPTION>
            </EVENT>
            EOS,
        );

        $this->assertEquals(
            'test test2',
            $event->description,
            'unexpected description',
        );
    }

    public function testIsOnline(): void
    {
        $event = $this->create('<EVENT digitalevent="online"></EVENT>');
        $this->assertTrue($event->online, 'unexpected online');
    }

    public function testIsOnlineWithTicketLink(): void
    {
        $event = $this->create(
            <<<EOS
            <EVENT>
                <TICKETLINK>https://www.ticket.com</TICKETLINK>
            </EVENT>
            EOS,
        );
        $this->assertFalse($event->online, 'unexpected online');
    }

    public function testIsOnlineWithHybrid(): void
    {
        $event = $this->create('<EVENT digitalevent="hybrid"></EVENT>');
        $this->assertTrue($event->online, 'unexpected online');
    }

    public function testIsOnsite(): void
    {
        $event = $this->create('<EVENT digitalevent="onsite"></EVENT>');
        $this->assertTrue($event->onsite, 'unexpected onsite');
    }

    public function testIsHighlight(): void
    {
        $event = $this->create(
            <<<EOS
            <EVENT>
                <DESCRIPTION highlight="yes"></DESCRIPTION>
            </EVENT>
            EOS,
        );
        $this->assertTrue($event->highlight, 'unexpected highlight');
    }

    public function testTicketLink(): void
    {
        $event = $this->create(
            <<<EOS
            <EVENT>
                <TICKETLINK>https://www.ticket.com</TICKETLINK>
            </EVENT>
            EOS,
        );
        $this->assertEquals(
            'https://www.ticket.com',
            $event->ticketLink,
            'unexpected ticketLink',
        );
    }

    public function testTheme(): void
    {
        $event = $this->create(
            <<<EOS
            <EVENT>
                <THEME id="1">Test</THEME>
            </EVENT>
            EOS,
        );
        $this->assertEquals(
            new RceEventTheme('1', 'Test'),
            $event->theme,
            'unexpected theme',
        );
    }

    public function testSubTheme(): void
    {
        $event = $this->create(
            <<<EOS
            <EVENT>
                <SUBTHEME id="1">Test</SUBTHEME>
            </EVENT>
            EOS,
        );
        $this->assertEquals(
            new RceEventTheme('1', 'Test'),
            $event->subTheme,
            'unexpected subTheme',
        );
    }

    public function testKeywords(): void
    {
        $event = $this->create(
            <<<EOS
            <EVENT>
                <KEYWORD>Test</KEYWORD>
            </EVENT>
            EOS,
        );
        $this->assertEquals(
            'Test',
            $event->keywords,
            'unexpected keywords',
        );
    }

    public function testSource(): void
    {
        $event = $this->create('<EVENT userid="1" supply="Test"></EVENT>');
        $this->assertEquals(
            new RceEventSource('1', 'Test'),
            $event->source,
            'unexpected source',
        );
    }

    public function testAddresses(): void
    {
        $event = $this->create(
            <<<EOS
            <EVENT>
                <ADDRESSLIST>
                    <ADDRESS>
                      <ZIP>34117</ZIP>
                      <CITY>Kassel</CITY>
                      <GEMKEY>6611000</GEMKEY>
                    </ADDRESS>
                    <ADDRESS type="presenter">
                      <NAME>Kassel Marketing - Erlebnisangebote</NAME>
                      <STREET>Obere Königsstraße 15</STREET>
                      <ZIP>34117</ZIP>
                      <CITY>Kassel</CITY>
                    </ADDRESS>
                </ADDRESSLIST>
            </EVENT>
            EOS,
        );

        $location = new RceEventAddress(
            '',
            '6611000',
            '',
            '34117',
            'Kassel',
        );

        $organizer = new RceEventAddress(
            'Kassel Marketing - Erlebnisangebote',
            '',
            'Obere Königsstraße 15',
            '34117',
            'Kassel',
        );

        $addresses = new RceEventAddresses($location, $organizer);
        $this->assertEquals(
            $addresses,
            $event->addresses,
            'unexpected addresses',
        );
    }

    public function testUploads(): void
    {
        $event = $this->create(
            <<<EOS
            <EVENT>
                <UPLOADLIST>
                    <UPLOAD>
                        <NAME>Eventbild 1</NAME>
                        <URL>https://event.com/image.png</URL>
                        <COPYRIGHT>© Kassel Marketing GmbH</COPYRIGHT>
                    </UPLOAD>
                    <UPLOAD>
                        <NAME>Eventbild 2</NAME>
                        <URL></URL>
                        <COPYRIGHT>© Kassel Marketing GmbH</COPYRIGHT>
                    </UPLOAD>
                </UPLOADLIST>
            </EVENT>
            EOS,
        );

        $upload = new RceEventUpload(
            'Eventbild 1',
            'https://event.com/image.png',
            '© Kassel Marketing GmbH',
        );

        $this->assertEquals(
            [$upload],
            $event->uploads,
            'unexpected uploads',
        );
    }

    private function create(string $xml): RceEventListItem
    {
        return $this->factory->create($this->toXml($xml));
    }

    private function toXml(string $xml): \SimpleXMLElement
    {
        return new \SimpleXMLElement($xml);
    }
}
