<?php

declare(strict_types=1);

namespace Atoolo\EventsCalendar\Test\Service\Indexer;

use Atoolo\EventsCalendar\Dto\RceEvent\RceEventAddresses;
use Atoolo\EventsCalendar\Dto\RceEvent\RceEventDate;
use Atoolo\EventsCalendar\Dto\RceEvent\RceEventListItem;
use Atoolo\EventsCalendar\Service\Indexer\RceEventDocumentEnricher;
use Atoolo\EventsCalendar\Service\Indexer\RceEventIndexer;
use Atoolo\EventsCalendar\Service\Indexer\RceEventIndexerFilter;
use Atoolo\EventsCalendar\Service\RceEvent\RceEventListReader;
use Atoolo\Resource\DataBag;
use Atoolo\Resource\ResourceLanguage;
use Atoolo\Search\Dto\Indexer\IndexerConfiguration;
use Atoolo\Search\Service\Indexer\IndexerConfigurationLoader;
use Atoolo\Search\Service\Indexer\IndexerProgressHandler;
use Atoolo\Search\Service\Indexer\IndexingAborter;
use Atoolo\Search\Service\Indexer\IndexSchema2xDocument;
use Atoolo\Search\Service\Indexer\SolrIndexService;
use Atoolo\Search\Service\Indexer\SolrIndexUpdater;
use Atoolo\Search\Service\IndexName;
use DateTime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Solarium\QueryType\Update\Result as UpdateResult;

#[CoversClass(RceEventIndexer::class)]
class RceEventIndexerTest extends TestCase
{
    private RceEventIndexer $indexer;

    private RceEventIndexerFilter&Stub $filter;

    private SolrIndexService&MockObject $indexService;

    private IndexerProgressHandler&MockObject $progressHandler;

    private IndexerConfigurationLoader&MockObject $configLoader;

    private RceEventListReader&MockObject $rceEventListReader;

    private IndexingAborter&Stub $aborter;

    private RceEventDocumentEnricher&Stub $documentEnricher;

    private UpdateResult&Stub $updateResult;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $doc = $this->createStub(IndexSchema2xDocument::class);
        $this->documentEnricher = $this->createMock(
            RceEventDocumentEnricher::class,
        );
        $this->documentEnricher->method('enrichDocument')
            ->willReturn($doc);
        $documentEnricherList = [$this->documentEnricher];
        $this->progressHandler = $this->createMock(
            IndexerProgressHandler::class,
        );
        $this->aborter = $this->createStub(IndexingAborter::class);

        $events = [
            $this->createRceEventListItem('test-1'),
            $this->createRceEventListItem('test-2'),
        ];

        $this->rceEventListReader = $this->createMock(
            RceEventListReader::class,
        );
        $this->rceEventListReader->method('getItems')->willReturn($events);
        $updater = $this->createMock(SolrIndexUpdater::class);
        $updater->method('createDocument')
            ->willReturn($doc);
        $this->updateResult = $this->createStub(UpdateResult::class);
        $updater->method('update')
            ->willReturn($this->updateResult);
        $this->indexService = $this->createMock(SolrIndexService::class);
        $this->indexService->method('updater')->willReturn($updater);

        $index = $this->createStub(IndexName::class);
        $this->configLoader = $this->createMock(
            IndexerConfigurationLoader::class,
        );
        $this->filter = $this->createStub(RceEventIndexerFilter::class);

        $this->indexer = new RceEventIndexer(
            $documentEnricherList,
            $this->progressHandler,
            $this->aborter,
            $this->rceEventListReader,
            $this->indexService,
            $index,
            $this->configLoader,
            $this->filter,
            'test',
        );
    }

    public function testIndex(): void
    {
        $config = $this->createConfig();
        $this->configLoader
            ->method('load')
            ->willReturn($config);
        $this->updateResult->method('getStatus')
            ->willReturn(0);

        $this->indexService->expects($this->once())
            ->method('deleteExcludingProcessId');
        $this->indexService->expects($this->once())
            ->method('commit');
        $this->progressHandler->expects($this->once())
            ->method('finish');
        $this->configLoader
            ->expects($this->once())
            ->method('load');
        $this->filter
            ->method('accept')
            ->willReturn(true);

        $this->indexer->index();
    }

    public function testIndexWithoutExportUrl(): void
    {
        $config = $this->createConfig([
            'exportUrl' => '',
        ]);

        $this->configLoader
            ->method('load')
            ->willReturn($config);
        $this->updateResult->method('getStatus')
            ->willReturn(0);

        $this->rceEventListReader->expects($this->never())
            ->method('read');

        $this->indexer->index();
    }

    public function testIndexWithAbortionRequested(): void
    {
        $config = $this->createConfig();
        $this->configLoader
            ->method('load')
            ->willReturn($config);
        $this->updateResult->method('getStatus')
            ->willReturn(0);

        $this->aborter->method('isAbortionRequested')
            ->willReturn(true);

        $this->progressHandler->expects($this->never())
            ->method('advance');

        $this->indexer->index();
    }

    public function testIndexWithEnrichDocumentError(): void
    {
        $config = $this->createConfig();
        $this->configLoader
            ->method('load')
            ->willReturn($config);
        $this->updateResult->method('getStatus')
            ->willReturn(0);

        $this->documentEnricher->method('enrichDocument')
            ->willThrowException(new \Exception('error'));

        $this->progressHandler->expects($this->exactly(2))
            ->method('error');

        $this->filter
            ->method('accept')
            ->willReturn(true);

        $this->indexer->index();
    }

    public function testIndexWithFailedSolrUpdate(): void
    {
        $config = $this->createConfig();
        $this->configLoader
            ->method('load')
            ->willReturn($config);
        $this->updateResult->method('getStatus')
            ->willReturn(1);

        $this->progressHandler->expects($this->once())
            ->method('error');

        $this->indexer->index();
    }

    public function testRemove(): void
    {
        $this->indexService->expects($this->once())
            ->method('deleteByIdListForAllLanguages')
            ->with('test', ['a', 'b']);

        $this->indexer->remove(['a', 'b']);
    }

    public function testGetIndex(): void
    {
        $lang = ResourceLanguage::default();
        $this->indexService->expects($this->once())
            ->method('getIndex')
            ->with($lang);
        $this->indexer->getIndex($lang);
    }

    private function createRceEventListItem(string $id): RceEventListItem
    {
        $startDate = new DateTime();
        $startDate->setDate(2024, 6, 25);
        $startDate->setTime(12, 0);

        $endDate = new DateTime();
        $endDate->setDate(2024, 6, 25);
        $endDate->setTime(16, 0);

        $eventDate = new RceEventDate(
            'hash',
            $startDate,
            $endDate,
            false,
            false,
            false,
            postponed: false,
        );
        $addresses = new RceEventAddresses();
        return new RceEventListItem(
            $id,
            'name-' . $id,
            true,
            [$eventDate],
            'description',
            true,
            true,
            'https://www.example.com/ticket',
            null,
            null,
            false,
            null,
            $addresses,
            '',
            [],
        );
    }

    private function createConfig(array $overrides = []): IndexerConfiguration
    {
        $data = array_merge(
            [
                'instanceList' => [
                    [
                        'id' => 1,
                        'detailPageUrl' => 'https://www.example.com/detail.php',
                        'group' => 3,
                        'groupPath' => [1,2,3],
                    ],
                ],
                'categoryRootResourceLocations' => [
                    '/path/to/category/root.php',
                ],
                'highlightCategory' => 1234,
                'cleanupThreshold' => 1,
                'exportUrl' => 'https://www.example.com/export.zip',
            ],
            $overrides,
        );
        return new IndexerConfiguration(
            'test',
            'test',
            new DataBag($data),
        );
    }
}
