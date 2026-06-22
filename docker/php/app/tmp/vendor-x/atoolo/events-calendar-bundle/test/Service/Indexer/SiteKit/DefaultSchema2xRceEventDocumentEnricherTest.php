<?php

declare(strict_types=1);

namespace Atoolo\EventsCalendar\Test\Service\Indexer\SiteKit;

use Atoolo\EventsCalendar\Dto\Indexer\RceEventIndexerInstance;
use Atoolo\EventsCalendar\Dto\Indexer\RceEventIndexerParameter;
use Atoolo\EventsCalendar\Dto\RceEvent\RceEventAddress;
use Atoolo\EventsCalendar\Dto\RceEvent\RceEventAddresses;
use Atoolo\EventsCalendar\Dto\RceEvent\RceEventDate;
use Atoolo\EventsCalendar\Dto\RceEvent\RceEventListItem;
use Atoolo\EventsCalendar\Dto\RceEvent\RceEventSource;
use Atoolo\EventsCalendar\Dto\RceEvent\RceEventTheme;
use Atoolo\EventsCalendar\Dto\RceEvent\RceEventUpload;
use Atoolo\EventsCalendar\Service\Indexer\{
    SiteKit\DefaultSchema2xRceEventDocumentEnricher
};
use Atoolo\Resource\DataBag;
use Atoolo\Resource\Resource;
use Atoolo\Resource\ResourceHierarchyLoader;
use Atoolo\Resource\ResourceLanguage;
use Atoolo\Resource\ResourceLocation;
use Atoolo\Search\Service\Indexer\IndexSchema2xDocument;
use DateTime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DefaultSchema2xRceEventDocumentEnricher::class)]
class DefaultSchema2xRceEventDocumentEnricherTest extends TestCase
{
    private array $rootResources = [];

    private array $resourceMap = [];

    private array $childrenResourceMap = [];

    private array $primaryPathMap = [];

    private DefaultSchema2xRceEventDocumentEnricher $enricher;

    private RceEventIndexerParameter $parameter;

    public function setUp(): void
    {
        $loader = $this->createHierarchyLoader();

        $instance = new RceEventIndexerInstance(
            1,
            'https://www.example.com/details.php',
            3,
            [1, 2, 3],
        );
        $this->parameter = new RceEventIndexerParameter(
            'test',
            [$instance],
            $this->rootResources,
            [
                'highlight' => [111],
            ],
            1,
            '',
        );

        $this->enricher = new DefaultSchema2xRceEventDocumentEnricher($loader);
    }

    public function testCleanUp(): void
    {
        $loader = $this->createMock(ResourceHierarchyLoader::class);
        $loader->expects($this->once())
            ->method('cleanup');

        $enricher = new DefaultSchema2xRceEventDocumentEnricher($loader);
        $enricher->cleanUp();
    }

    public function testEnrichDocument(): void
    {
        $event = $this->createEvent();

        $doc = new IndexSchema2xDocument();

        $enrichedDoc = $this->enricher->enrichDocument(
            $this->parameter,
            $this->parameter->instanceList[0],
            $event,
            $event->dates[0],
            $doc,
            'process-id',
        );

        $expected = new IndexSchema2xDocument();
        $expected->sp_name = 'myname';
        $expected->title = 'myname';
        $expected->sp_title = 'myname';
        $expected->sp_sortvalue = 'myname';
        $expected->description = 'description';
        $expected->crawl_process_id = 'process-id';
        $expected->id = 'test-1-123-hash';
        $expected->url = 'https://www.example.com/details.php?id=hash';
        $expected->contenttype = 'text/html; charset=UTF-8';
        $expected->sp_contenttype = [
            'eventsCalendar-event',
            'schedule',
            'schedule_single',
            'schedule_start',
            'schedule_end',
        ];
        $expected->sp_changed = $event->dates[0]->startDate;
        $expected->sp_date = $event->dates[0]->startDate;
        $expected->sp_date_from = $event->dates[0]->startDate;
        $expected->sp_date_to = $event->dates[0]->endDate;
        $expected->sp_date_list = [$event->dates[0]->startDate];
        $expected->sp_group = 3;
        $expected->sp_group_path = [1, 2, 3];
        $expected->sp_source = ['test'];
        $expected->sp_category = [111];
        $expected->sp_category_path = [111];
        $expected->keywords = ['keyword'];
        $expected->content =
            'location-name location-street location-zip location-city';
        $expected->setMetaString(
            'event_ticketLink',
            'https://www.example.com/ticket',
        );
        $expected->setMetaString('imageUrl', []);
        $expected->setMetaBool('event_cancelled', true);
        $expected->setMetaBool('event_soldout', true);
        $expected->setMetaBool('event_postponed', true);
        $expected->setMetaString('event_location', 'location-name');
        $expected->setMetaText('event_rce_location', 'location-name');
        $expected->setMetaText('event_rce_organizer', 'organizer-name');
        $expected->setMetaString('imageUrl', ['test.png']);

        $this->assertEquals(
            $expected,
            $enrichedDoc,
            'Document should be enriched with the correct data.',
        );
    }

    public function testEnrichDocumentWithOnlineEvent(): void
    {
        $event = $this->createEvent(true);

        $doc = new IndexSchema2xDocument();

        $enrichedDoc = $this->enricher->enrichDocument(
            $this->parameter,
            $this->parameter->instanceList[0],
            $event,
            $event->dates[0],
            $doc,
            'process-id',
        );

        $fields = $enrichedDoc->getFields();
        $this->assertEquals(
            'https://www.example.com/ticket',
            $fields['sp_meta_string_event_streamingLink'],
        );
    }

    public function testEnrichDocumentWithTheme(): void
    {
        $theme = new RceEventTheme('12', 'Ausstellung');
        $event = $this->createEvent(
            false,
            $theme,
        );

        $doc = new IndexSchema2xDocument();

        $enrichedDoc = $this->enricher->enrichDocument(
            $this->parameter,
            $this->parameter->instanceList[0],
            $event,
            $event->dates[0],
            $doc,
            'process-id',
        );

        $fields = $enrichedDoc->getFields();
        $expected = [
            'sp_meta_string_kicker' => 'Ausstellung',
            'sp_category' => [12, 111],
            'sp_category_path' => [10, 12, 111],
        ];

        $this->assertEquals(
            $expected,
            @array_intersect_assoc($fields, $expected),
        );
    }

    public function testEnrichDocumentWithThemeNotFound(): void
    {
        $theme = new RceEventTheme('99', 'Abc');
        $event = $this->createEvent(
            false,
            $theme,
        );

        $doc = new IndexSchema2xDocument();

        $enrichedDoc = $this->enricher->enrichDocument(
            $this->parameter,
            $this->parameter->instanceList[0],
            $event,
            $event->dates[0],
            $doc,
            'process-id',
        );

        $fields = $enrichedDoc->getFields();
        $expected = [
            'sp_category' => [111],
            'sp_category_path' => [111],
        ];

        $this->assertEquals(
            $expected,
            @array_intersect_assoc($fields, $expected),
        );
    }


    public function testEnrichDocumentWithThemeAndSubTheme(): void
    {
        $theme = new RceEventTheme('12', 'Ausstellung');
        $subTheme = new RceEventTheme('13', 'Film & Medien');
        $event = $this->createEvent(
            false,
            $theme,
            $subTheme,
        );

        $doc = new IndexSchema2xDocument();

        $enrichedDoc = $this->enricher->enrichDocument(
            $this->parameter,
            $this->parameter->instanceList[0],
            $event,
            $event->dates[0],
            $doc,
            'process-id',
        );

        $fields = $enrichedDoc->getFields();
        $expected = [
            'sp_meta_string_kicker' => 'Ausstellung',
            'sp_category' => [13, 111],
            'sp_category_path' => [10, 12, 13, 111],
        ];

        $this->assertEquals(
            $expected,
            @array_intersect_assoc($fields, $expected),
        );
    }

    public function testEnrichDocumentWithThemeAndSubThemeNoParent(): void
    {
        $theme = new RceEventTheme('12', 'Ausstellung');
        $subTheme = new RceEventTheme('15', 'No Parent');
        $event = $this->createEvent(
            false,
            $theme,
            $subTheme,
        );

        $doc = new IndexSchema2xDocument();

        $enrichedDoc = $this->enricher->enrichDocument(
            $this->parameter,
            $this->parameter->instanceList[0],
            $event,
            $event->dates[0],
            $doc,
            'process-id',
        );

        $fields = $enrichedDoc->getFields();
        $expected = [
            'sp_meta_string_kicker' => 'Ausstellung',
            'sp_category' => [15, 111],
            'sp_category_path' => [111],
        ];

        $this->assertEquals(
            $expected,
            @array_intersect_assoc($fields, $expected),
        );
    }

    public function testEnrichDocumentWithThemeAndSubThemeNotFound(): void
    {
        $theme = new RceEventTheme('12', 'Ausstellung');
        $subTheme = new RceEventTheme('99', 'Abc');
        $event = $this->createEvent(
            false,
            $theme,
            $subTheme,
        );

        $doc = new IndexSchema2xDocument();

        $enrichedDoc = $this->enricher->enrichDocument(
            $this->parameter,
            $this->parameter->instanceList[0],
            $event,
            $event->dates[0],
            $doc,
            'process-id',
        );

        $fields = $enrichedDoc->getFields();
        $expected = [
            'sp_meta_string_kicker' => 'Ausstellung',
            'sp_category' => [12, 111],
            'sp_category_path' => [10, 12, 111],
        ];

        $this->assertEquals(
            $expected,
            @array_intersect_assoc($fields, $expected),
        );
    }

    public function testEnrichDocumentWithThemeNotFoundAndSubTheme(): void
    {
        $theme = new RceEventTheme('99', 'Abc');
        $subTheme = new RceEventTheme('13', 'Film & Medien');
        $event = $this->createEvent(
            false,
            $theme,
            $subTheme,
        );

        $doc = new IndexSchema2xDocument();

        $enrichedDoc = $this->enricher->enrichDocument(
            $this->parameter,
            $this->parameter->instanceList[0],
            $event,
            $event->dates[0],
            $doc,
            'process-id',
        );

        $fields = $enrichedDoc->getFields();
        $expected = [
            'sp_category' => [13, 111],
            'sp_category_path' => [10, 12, 13, 111],
        ];

        $this->assertEquals(
            $expected,
            @array_intersect_assoc($fields, $expected),
        );
    }

    public function testEnrichDocumentWithThemeAndSubThemeNoSubCategory(): void
    {
        $theme = new RceEventTheme('12', 'Ausstellung');
        $subTheme = new RceEventTheme('14', 'Konzert');
        $event = $this->createEvent(
            false,
            $theme,
            $subTheme,
        );

        $doc = new IndexSchema2xDocument();

        $enrichedDoc = $this->enricher->enrichDocument(
            $this->parameter,
            $this->parameter->instanceList[0],
            $event,
            $event->dates[0],
            $doc,
            'process-id',
        );

        $fields = $enrichedDoc->getFields();
        $expected = [
            'sp_meta_string_kicker' => 'Ausstellung',
            'sp_category' => [14, 12, 111],
            'sp_category_path' => [10, 14, 10, 12, 111],
        ];

        $this->assertEquals(
            $expected,
            @array_intersect_assoc($fields, $expected),
        );
    }

    public function testEnrichDocumentWithSource(): void
    {
        $source = new RceEventSource('1361', 'Staatstheater Kassel');
        $event = $this->createEvent(
            false,
            null,
            null,
            $source,
        );

        $doc = new IndexSchema2xDocument();

        $enrichedDoc = $this->enricher->enrichDocument(
            $this->parameter,
            $this->parameter->instanceList[0],
            $event,
            $event->dates[0],
            $doc,
            'process-id',
        );

        $fields = $enrichedDoc->getFields();
        $expected = [
            'sp_category' => [111, 21],
            'sp_category_path' => [111, 20, 21],
        ];

        $this->assertEquals(
            $expected,
            @array_intersect_assoc($fields, $expected),
        );
    }
    public function createEvent(
        bool $online = false,
        RceEventTheme $theme = null,
        RceEventTheme $subTheme = null,
        RceEventSource $source = null,
    ): RceEventListItem {
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
            true,
            true,
            true,
        );
        $addresses = new RceEventAddresses(
            new RceEventAddress(
                'location-name',
                'location-gemkey',
                'location-street',
                'location-zip',
                'location-city',
            ),
            new RceEventAddress(
                'organizer-name',
                'organizer-gemkey',
                'organizer-street',
                'organizer-zip',
                'organizer-city',
            ),
        );

        $upload = new RceEventUpload(
            'upload-name',
            'test.png',
            'upload-copyright',
        );
        $unsupportedUpload = new RceEventUpload(
            'unsupported-upload-name',
            'test.abc',
            'unsupported-upload-copyright',
        );
        $noDotUpload = new RceEventUpload(
            'no-dot-upload-name',
            'test',
            'no-dot-upload-copyright',
        );
        return new RceEventListItem(
            '123',
            'myname',
            true,
            [$eventDate],
            'description',
            $online,
            !$online,
            'https://www.example.com/ticket',
            $theme,
            $subTheme,
            true,
            $source,
            $addresses,
            'keyword',
            [$upload, $unsupportedUpload, $noDotUpload],
        );
    }

    private function createHierarchyLoader(): ResourceHierarchyLoader
    {
        $loader = $this->createStub(ResourceHierarchyLoader::class);

        $this->createTypeCategoryTree();
        $this->createSourceCategoryTree();
        $this->createGemeindeTree();

        $resourceMap = $this->resourceMap;
        $loader->method('load')
            ->willReturnCallback(
                function (ResourceLocation $location) use ($resourceMap) {
                    return $resourceMap[$location->location];
                },
            );

        $childrenResourceMap = $this->childrenResourceMap;
        $loader->method('getChildrenLocations')
            ->willReturnCallback(
                function (
                    Resource $resource,
                ) use ($childrenResourceMap) {
                    return $childrenResourceMap[$resource->location] ?? [];
                },
            );

        $primaryPathMap = $this->primaryPathMap;
        $loader->method('loadPrimaryPath')
            ->willReturnCallback(
                function (
                    ResourceLocation $location,
                ) use ($primaryPathMap) {
                    return $primaryPathMap[$location->location];
                },
            );

        $loader->method('loadPrimaryParent')
            ->willReturnCallback(
                function (
                    ResourceLocation $location,
                ) use ($primaryPathMap) {
                    $parentPath = $primaryPathMap[$location->location];
                    return $parentPath[count($parentPath) - 2] ?? null;
                },
            );

        return $loader;
    }

    private function createTypeCategoryTree(): void
    {
        $root = $this->createResource(
            '10',
            '/category/type/root.php',
            'type-root',
            'type-root',
        );
        $ausstellung = $this->createResource(
            '12',
            '/category/type/ausstellung.php',
            'rce.type.12',
            'Ausstellung',
        );
        $filmMedien = $this->createResource(
            '13',
            '/category/type/film-medien.php',
            'rce.type.13',
            'Film & Medien',
        );
        $konzert = $this->createResource(
            '14',
            '/category/type/konzert.php',
            'rce.type.14',
            'Konzert',
        );
        $noParent = $this->createResource(
            '15',
            '/category/type/no-parent.php',
            'rce.type.15',
            'No Parent',
        );

        $this->rootResources[] = $root->location;
        $this->resourceMap[$root->location] = $root;
        $this->resourceMap[$ausstellung->location] = $ausstellung;
        $this->resourceMap[$filmMedien->location] = $filmMedien;
        $this->resourceMap[$konzert->location] = $konzert;
        $this->resourceMap[$noParent->location] = $noParent;

        $this->childrenResourceMap[$root->location] = [
            $ausstellung,
            $filmMedien,
            $konzert,
            $noParent,
        ];
        $this->childrenResourceMap[$ausstellung->location] = [
            $filmMedien,
        ];

        $this->primaryPathMap[$root->location] = [$root];
        $this->primaryPathMap[$ausstellung->location] = [
            $root,
            $ausstellung,
        ];
        $this->primaryPathMap[$konzert->location] = [
            $root,
            $konzert,
        ];
        $this->primaryPathMap[$filmMedien->location] = [
            $root,
            $ausstellung,
            $filmMedien,
        ];
        $this->primaryPathMap[$noParent->location] = [];
    }

    private function createSourceCategoryTree(): void
    {
        $root = $this->createResource(
            '20',
            '/category/source/root.php',
            'source-root',
            'source-root',
        );
        $staatstheaterKassel = $this->createResource(
            '21',
            '/category/source/staatstheater-kassel.php',
            'rce.source.1361',
            'Staatstheater Kassel',
        );

        $this->rootResources[] = $root->location;
        $this->resourceMap[$root->location] = $root;
        $this->resourceMap[$staatstheaterKassel->location] =
            $staatstheaterKassel;
        $this->childrenResourceMap[$root->location] = [
            $staatstheaterKassel,
        ];
        $this->primaryPathMap[$root->location] = [$root];
        $this->primaryPathMap[$staatstheaterKassel->location] = [
            $root,
            $staatstheaterKassel,
        ];
    }

    private function createGemeindeTree(): void
    {
        $root = $this->createResource(
            '30',
            '/category/gem/root.php',
            'gem-root',
            'gem-root',
        );

        $this->rootResources[] = $root->location;
        $this->resourceMap[$root->location] = $root;
        $this->childrenResourceMap[$root->location] = [
        ];
        $this->primaryPathMap[$root->location] = [$root];
    }

    private function createResource(
        string $id,
        string $location,
        string $anchor,
        string $name,
    ): Resource {
        return new Resource(
            $location,
            $id,
            $name,
            'objectType',
            ResourceLanguage::default(),
            new DataBag([
                'anchor' => $anchor,
            ]),
        );
    }
}
