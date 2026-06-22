<?php

declare(strict_types=1);

namespace Atoolo\EventsCalendar\Service\RceEvent;

use Atoolo\EventsCalendar\Dto\RceEvent\RceEventListItem;
use Atoolo\EventsCalendar\Service\Platform;
use RuntimeException;
use SimpleXMLElement;
use ZipArchive;

class RceEventListReader
{
    /**
     * @var RceEventListItem[]
     */
    private array $items = [];

    public function __construct(
        private readonly string $workDir,
        private readonly RceEventListHttpClient $httpClient,
        private readonly RceEventListItemFactory $factory,
        private readonly Platform $platform,
    ) {}
    /**
     * Unzips the zip file from the URL and reads the contained
     * XML files with simplexml_load_file.
     */
    public function read(string $zip): void
    {
        $zipFile = $this->downloadZip($zip);
        try {
            $xml = $this->loadXmlFromZip($zip, $zipFile);
            $this->items = $this->readXml($xml);
        } finally {
            $this->platform->unlink($zipFile);
        }
    }

    /**
     * @return RceEventListItem[]
     */
    public function getItems(): array
    {
        return $this->items;
    }

    private function downloadZip(string $zipUrl): string
    {
        $temp = $this->getTmpFile('rce-events', '.zip');
        file_put_contents(
            $temp,
            $this->httpClient->get($zipUrl),
        );
        return $temp;
    }

    private function loadXmlFromZip(
        string $zipUrl,
        string $zipFile,
    ): SimpleXMLElement {
        $zip = new ZipArchive();
        try {
            $res = $zip->open($zipFile);
            if ($res !== true) {
                throw new RuntimeException(
                    'Unable to open zip',
                );
            }

            if ($zip->numFiles === 0) {
                throw new RuntimeException(
                    'The zip file contains no files:' .
                    $zipUrl,
                );
            }

            if ($zip->numFiles !== 1) {
                throw new RuntimeException(
                    'The zip file contains more than one file:' .
                    $zipUrl,
                );
            }

            $content = $zip->getFromIndex(0);
            // unknown how to test it
            // @codeCoverageIgnoreStart
            if ($content === false) {
                throw new RuntimeException(
                    'No entry found in zip: ' .
                    $zipUrl,
                );
            }
            // @codeCoverageIgnoreEnd

            $xml = @simplexml_load_string($content);
            if ($xml === false) {
                throw new RuntimeException(
                    'Unable to parse XML from zip file:' .
                    $zipUrl,
                );
            }

            return $xml;
        } finally {
            $zip->close();
        }
    }

    /**
     * @return RceEventListItem[]
     */
    private function readXml(SimpleXMLElement $xml): array
    {
        $eventList = [];
        foreach ($xml->EVENTLIST->EVENT as $event) {
            $eventList[] = $this->factory->create($event);
        }
        return $eventList;
    }

    private function getTmpFile(string $prefix, string $suffix): string
    {
        $file = $this->platform->tempnam($this->getWorkDir(), $prefix);
        $fileWithSuffix = $file . $suffix;
        $this->platform->rename($file, $fileWithSuffix);
        return $fileWithSuffix;
    }

    private function getWorkDir(): string
    {
        if ($this->platform->is_dir($this->workDir)) {
            if (!$this->platform->is_writeable($this->workDir)) {
                throw new RuntimeException(
                    'Workdir is not writable ' . $this->workDir,
                );
            }
            return $this->workDir;
        }

        $this->platform->mkdir($this->workDir);
        return $this->workDir;
    }
}
