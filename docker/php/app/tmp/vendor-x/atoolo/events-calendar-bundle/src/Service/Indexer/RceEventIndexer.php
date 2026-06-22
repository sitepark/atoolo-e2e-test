<?php

declare(strict_types=1);

namespace Atoolo\EventsCalendar\Service\Indexer;

use Atoolo\EventsCalendar\Dto\Indexer\RceEventIndexerInstance;
use Atoolo\EventsCalendar\Dto\Indexer\RceEventIndexerParameter;
use Atoolo\EventsCalendar\Dto\RceEvent\RceEventDate;
use Atoolo\EventsCalendar\Dto\RceEvent\RceEventListItem;
use Atoolo\EventsCalendar\Service\RceEvent\RceEventListReader;
use Atoolo\Resource\ResourceLanguage;
use Atoolo\Search\Dto\Indexer\IndexerStatus;
use Atoolo\Search\Service\AbstractIndexer;
use Atoolo\Search\Service\Indexer\IndexDocument;
use Atoolo\Search\Service\Indexer\IndexerConfigurationLoader;
use Atoolo\Search\Service\Indexer\IndexerProgressHandler;
use Atoolo\Search\Service\Indexer\IndexingAborter;
use Atoolo\Search\Service\Indexer\IndexSchema2xDocument;
use Atoolo\Search\Service\Indexer\SolrIndexService;
use Atoolo\Search\Service\Indexer\SolrIndexUpdater;
use Atoolo\Search\Service\IndexName;
use Exception;
use Throwable;

class RceEventIndexer extends AbstractIndexer
{
    /**
     * phpcs:ignore
     * @param iterable<RceEventDocumentEnricher<IndexDocument>> $documentEnricherList
     */
    public function __construct(
        private readonly iterable $documentEnricherList,
        IndexerProgressHandler $progressHandler,
        IndexingAborter $aborter,
        private readonly RceEventListReader $rceEventListReader,
        private readonly SolrIndexService $indexService,
        IndexName $index,
        IndexerConfigurationLoader $configLoader,
        private readonly RceEventIndexerFilter $filter,
        string $source,
    ) {
        parent::__construct(
            $index,
            $progressHandler,
            $aborter,
            $configLoader,
            $source,
        );
    }

    public function getIndex(ResourceLanguage $lang): string
    {
        return $this->indexService->getIndex($lang);
    }

    public function index(): IndexerStatus
    {

        $parameter = $this->loadIndexerParameter();

        if (empty($parameter->exportUrl)) {
            return $this->progressHandler->getStatus();
        }

        $this->rceEventListReader->read($parameter->exportUrl);

        $this->progressHandler->start(
            $this->countEventDates() * count($parameter->instanceList),
        );

        $updater = $this->indexService->updater(ResourceLanguage::default());

        $processId = uniqid('', true);
        $successCount = 0;

        foreach ($parameter->instanceList as $instance) {
            foreach ($this->rceEventListReader->getItems() as $rceEvent) {
                if (
                    $this->isAbortionRequested()
                ) {
                    return $this->progressHandler->getStatus();
                }
                $successCount += $this->indexEvent(
                    $updater,
                    $parameter,
                    $instance,
                    $rceEvent,
                    $processId,
                );
            }
        }

        $result = $updater->update();
        if ($result->getStatus() !== 0) {
            $this->progressHandler->error(new Exception(
                $result->getResponse()->getStatusMessage(),
            ));
            $this->progressHandler->getStatus();
        }

        if (
            $successCount >= $parameter->cleanupThreshold
        ) {
            $this->indexService->deleteExcludingProcessId(
                ResourceLanguage::default(),
                $this->source,
                $processId,
            );
        }

        $this->indexService->commit(ResourceLanguage::default());

        $this->progressHandler->finish();
        gc_collect_cycles();

        return $this->progressHandler->getStatus();
    }

    private function loadIndexerParameter(): RceEventIndexerParameter
    {
        $config = $this->configLoader->load($this->source);
        $data = $config->data;

        /** @var array<int> $groupPath */
        $groupPath = $data->getArray('groupPath');

        /** @var array<string> $categoryRootResourceLocations */
        $categoryRootResourceLocations =
            $data->getArray('categoryRootResourceLocations');

        $instanceList = [];
        /** @var array{
         *     id:int,
         *     detailPageUrl: string,
         *     group:int,
         *     groupPath: array<int>,
         *     kickerCategoryResourceLocation: ?string
         * } $instance
         */
        foreach ($data->getArray('instanceList') as $instance) {
            $instanceList[] = new RceEventIndexerInstance(
                $instance['id'],
                $instance['detailPageUrl'],
                $instance['group'],
                $instance['groupPath'],
                $instance['kickerCategoryResourceLocation'] ?? null,
            );
        }

        /** @var array<string,array<int,int>> $simpleCategoryMap */
        $simpleCategoryMap = $data->getAssociativeArray('simpleCategoryMap');
        // for backward compatibility
        $highlightCategory = $data->getInt('highlightCategory', -1);
        if ($highlightCategory !== -1 && !isset($simpleCategoryMap['highlight'])) {
            $simpleCategoryMap['highlight'] = [$highlightCategory];
        }
        return new RceEventIndexerParameter(
            source: $this->source,
            instanceList: $instanceList,
            categoryRootResourceLocations: $categoryRootResourceLocations,
            simpleCategoryMap: $simpleCategoryMap,
            cleanupThreshold: $data->getInt('cleanupThreshold'),
            exportUrl: $data->getString('exportUrl'),
        );
    }

    private function indexEvent(
        SolrIndexUpdater $updater,
        RceEventIndexerParameter $parameter,
        RceEventIndexerInstance $instance,
        RceEventListItem $event,
        string $processId,
    ): int {

        $count = 0;
        foreach ($event->dates as $eventDate) {
            if ($this->filter->accept($event, $eventDate) === false) {
                $this->progressHandler->skip(1);
                continue;
            }
            $count += $this->indexEventDate(
                $updater,
                $parameter,
                $instance,
                $event,
                $eventDate,
                $processId,
            );
        }
        return $count;
    }

    private function indexEventDate(
        SolrIndexUpdater $updater,
        RceEventIndexerParameter $parameter,
        RceEventIndexerInstance $instance,
        RceEventListItem $event,
        RceEventDate $eventDate,
        string $processId,
    ): int {

        $this->progressHandler->advance(1);

        $count = 0;
        try {
            /** @var IndexSchema2xDocument $doc */
            $doc = $updater->createDocument();
            foreach ($this->documentEnricherList as $enricher) {
                /** @var IndexSchema2xDocument $doc */
                $doc = $enricher->enrichDocument(
                    $parameter,
                    $instance,
                    $event,
                    $eventDate,
                    $doc,
                    $processId,
                );
            }

            $count++;
            $updater->addDocument($doc);
        } catch (Throwable $e) {
            $this->progressHandler->error($e);
        }

        return $count;
    }

    private function countEventDates(): int
    {
        $count = 0;
        foreach ($this->rceEventListReader->getItems() as $rceEvent) {
            $count += count($rceEvent->dates);
        }
        return $count;
    }

    public function remove(array $idList): void
    {
        $this->indexService->deleteByIdListForAllLanguages(
            $this->source,
            $idList,
        );
    }
}
