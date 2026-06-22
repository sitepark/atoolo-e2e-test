<?php

declare(strict_types=1);

namespace Atoolo\EventsCalendar\Test\Service\RceEvent;

use Atoolo\EventsCalendar\Dto\RceEvent\RceEventListItem;
use Atoolo\EventsCalendar\Service\Platform;
use Atoolo\EventsCalendar\Service\RceEvent\RceEventListHttpClient;
use Atoolo\EventsCalendar\Service\RceEvent\RceEventListItemFactory;
use Atoolo\EventsCalendar\Service\RceEvent\RceEventListReader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use ValueError;

#[CoversClass(RceEventListReader::class)]
class RceEventListReaderTest extends TestCase
{
    private string $workDir =
        __DIR__ . '/../../../var/test/RceEventListReaderTest';

    private Platform $platform;

    private RceEventListItem $event;
    private RceEventListItemFactory $factory;
    private RceEventListHttpClient $httpClient;

    public function setup(): void
    {
        $this->event = $this->createStub(RceEventListItem::class);
        $this->factory = $this->createStub(RceEventListItemFactory::class);
        $this->factory->method('create')->willReturn($this->event);
        $this->httpClient = $this->createStub(RceEventListHttpClient::class);
        $this->platform = new Platform();
    }

    public function tearDown(): void
    {
        $this->rrmdir($this->workDir);
    }

    private function rrmdir($dir): void
    {
        chmod($dir, 0777);

        if (!is_dir($dir)) {
            return;
        }
        $objects = scandir($dir);
        if ($objects === false) {
            return;
        }
        foreach ($objects as $object) {
            if ($object === "." || $object === "..") {
                continue;
            }
            $path = $dir . DIRECTORY_SEPARATOR . $object;
            chmod($path, 0777);
            if (is_dir($path) && !is_link($path)) {
                $this->rrmdir($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
    public function testReadZipUrlWithExistsWorkdir(): void
    {
        mkdir($this->workDir);
        $this->httpClient->method('get')->willReturnCallback(
            function (string $url): string {
                $zip = __DIR__ .
                    '/../../resources/RceEventListReader/rce-xml_neu.zip';
                return file_get_contents($zip);
            },
        );

        $platform = $this->createStub(Platform::class);

        $reader = new RceEventListReader(
            $this->workDir,
            $this->httpClient,
            $this->factory,
            $this->platform,
        );
        $reader->read('https://dummy.url');

        $items = $reader->getItems();
        $this->assertEquals(
            $this->event,
            $items[0],
            'unexpected event in list',
        );
    }

    public function testReadWithInvalidZipData(): void
    {
        $this->httpClient->method('get')->willReturnCallback(
            function (string $url): string {
                return "nozipdata";
            },
        );

        $platform = $this->createStub(Platform::class);

        $reader = new RceEventListReader(
            $this->workDir,
            $this->httpClient,
            $this->factory,
            $this->platform,
        );
        $this->expectException(ValueError::class);
        $reader->read('https://dummy.url');
    }

    public function testReadWithEmptyZipFile(): void
    {
        $this->httpClient->method('get')->willReturnCallback(
            function (string $url): string {
                $zip = __DIR__ .
                    '/../../resources/RceEventListReader/empty.zip';
                return file_get_contents($zip);
            },
        );

        $platform = $this->createStub(Platform::class);

        $reader = new RceEventListReader(
            $this->workDir,
            $this->httpClient,
            $this->factory,
            $this->platform,
        );
        $this->expectException(RuntimeException::class);
        $reader->read('https://dummy.url');
    }

    public function testReadWithZipContainsTwoFiles(): void
    {
        $this->httpClient->method('get')->willReturnCallback(
            function (string $url): string {
                $zip = __DIR__ .
                    '/../../resources/RceEventListReader/two-files.zip';
                return file_get_contents($zip);
            },
        );

        $platform = $this->createStub(Platform::class);

        $reader = new RceEventListReader(
            $this->workDir,
            $this->httpClient,
            $this->factory,
            $this->platform,
        );
        $this->expectException(RuntimeException::class);
        $reader->read('https://dummy.url');
    }

    public function testReadWithZipContainsNonXmlFile(): void
    {
        $this->httpClient->method('get')->willReturnCallback(
            function (string $url): string {
                $zip = __DIR__ .
                    '/../../resources/RceEventListReader/noxml.zip';
                return file_get_contents($zip);
            },
        );

        $platform = $this->createStub(Platform::class);

        $reader = new RceEventListReader(
            $this->workDir,
            $this->httpClient,
            $this->factory,
            $this->platform,
        );
        $this->expectException(RuntimeException::class);
        $reader->read('https://dummy.url');
    }

    public function testReadWithNonWritableWorkdir(): void
    {

        $base = $this->workDir;
        $workDir = $base . '/nonwritable';

        mkdir($base, 0777, true);
        mkdir($workDir, 0000, true);

        $reader = new RceEventListReader(
            $workDir,
            $this->httpClient,
            $this->factory,
            $this->platform,
        );
        $this->expectException(RuntimeException::class);
        $reader->read('https://dummy.url');
    }

    public function testReadWithNonCreatableWorkdir(): void
    {
        $base = $this->workDir;
        mkdir($base, 0000, true);

        $workDir = $base . '/noncreatable';

        $reader = new RceEventListReader(
            $workDir,
            $this->httpClient,
            $this->factory,
            $this->platform,
        );
        $this->expectException(RuntimeException::class);
        $reader->read('https://dummy.url');
    }
}
