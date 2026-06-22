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
use Atoolo\EventsCalendar\Service\Indexer\SiteKit\DefaultSchema2xRceEventCategoryDocumentEnricher;
use Atoolo\Resource\DataBag;
use Atoolo\Resource\Resource;
use Atoolo\Resource\ResourceHierarchyLoader;
use Atoolo\Resource\ResourceLanguage;
use Atoolo\Resource\ResourceLocation;
use Atoolo\Search\Service\Indexer\IndexSchema2xDocument;
use DateTime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DefaultSchema2xRceEventCategoryDocumentEnricher::class)]
class DefaultSchema2xRceEventCategoryDocumentEnricherTest extends TestCase
{
    private ?string $kickerCategoryResourceLocation = null;

    private array $resourceMap = [];

    private array $childrenResourceMap = [];

    private array $primaryPathMap = [];

    private DefaultSchema2xRceEventCategoryDocumentEnricher $enricher;

    private RceEventIndexerParameter $parameter;

    public function setUp(): void
    {
        $loader = $this->createHierarchyLoader();

        $instance = new RceEventIndexerInstance(
            1,
            'https://www.example.com/details.php',
            3,
            [1, 2, 3],
            $this->kickerCategoryResourceLocation,
        );
        $this->parameter = new RceEventIndexerParameter(
            'test',
            [$instance],
            [],
            [
                'highlight' => [111],
            ],
            1,
            '',
        );

        $this->enricher = new DefaultSchema2xRceEventCategoryDocumentEnricher($loader);
    }

    public function testCleanUp(): void
    {
        $loader = $this->createMock(ResourceHierarchyLoader::class);
        $loader->expects($this->once())
            ->method('cleanup');

        $enricher = new DefaultSchema2xRceEventCategoryDocumentEnricher($loader);
        $enricher->cleanUp();
    }

    public function testEnrichDocument(): void
    {
        $event = $this->createEvent();
        $doc = new IndexSchema2xDocument();
        $doc->sp_category = ['11', '12'];
        $enrichedDoc = $this->enricher->enrichDocument(
            $this->parameter,
            $this->parameter->instanceList[0],
            $event,
            $event->dates[0],
            $doc,
            'process-id',
        );

        $this->assertEquals(
            'some-category-a | some-category-b',
            $enrichedDoc->getFields()['sp_meta_string_kicker'],
            'Document should be enriched with the correct data.',
        );
    }

    public function createEvent(): RceEventListItem
    {
        $eventDate = new RceEventDate(
            'hash',
            new DateTime(),
            new DateTime(),
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
        return new RceEventListItem(
            '123',
            'myname',
            true,
            [$eventDate],
            'description',
            false,
            false,
            'https://www.example.com/ticket',
            null,
            null,
            true,
            null,
            $addresses,
            'keyword',
            [],
        );
    }

    private function createHierarchyLoader(): ResourceHierarchyLoader
    {
        $loader = $this->createStub(ResourceHierarchyLoader::class);
        $this->createCategoryTree();
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

    private function createCategoryTree(): void
    {
        $root = $this->createResource(
            '10',
            '/category/root.php',
            'some-category-root',
            'some-category-root',
        );
        $childA = $this->createResource(
            '11',
            '/category/childA.php',
            'some-category-a',
            'some-category-a',
        );
        $childB = $this->createResource(
            '12',
            '/category/childB.php',
            'some-category-b',
            'some-category-b',
        );

        $this->kickerCategoryResourceLocation = $root->location;
        $this->resourceMap[$root->location] = $root;
        $this->resourceMap[$childA->location] = $childA;
        $this->resourceMap[$childB->location] = $childB;

        $this->childrenResourceMap[$root->location] = [
            $childA,
            $childB,
        ];
        $this->primaryPathMap[$root->location] = [$root];
        $this->primaryPathMap[$childA->location] = [
            $root,
            $childA,
        ];
        $this->primaryPathMap[$childB->location] = [
            $root,
            $childB,
        ];
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
                'base' => [
                    'title' => $name,
                ],
            ]),
        );
    }
}
