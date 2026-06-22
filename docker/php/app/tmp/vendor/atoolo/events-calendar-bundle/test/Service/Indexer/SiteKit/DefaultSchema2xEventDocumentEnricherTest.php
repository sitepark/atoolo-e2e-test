<?php

declare(strict_types=1);

namespace Atoolo\EventsCalendar\Test\Service\Indexer\SiteKit;

use Atoolo\EventsCalendar\Service\Indexer\SiteKit\DefaultSchema2xEventDocumentEnricher;
use Atoolo\EventsCalendar\Service\Indexer\SiteKit\DefaultSchema2xRceEventDocumentEnricher;
use Atoolo\Resource\DataBag;
use Atoolo\Resource\Resource;
use Atoolo\Resource\ResourceLanguage;
use Atoolo\Search\Service\Indexer\IndexSchema2xDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(DefaultSchema2xEventDocumentEnricher::class)]
class DefaultSchema2xEventDocumentEnricherTest extends TestCase
{
    private DefaultSchema2xEventDocumentEnricher $enricher;

    public function setUp(): void
    {
        $this->enricher = new DefaultSchema2xEventDocumentEnricher();
    }

    public function testCleanup(): void
    {
        $this->expectNotToPerformAssertions();
        $this->enricher->cleanup();
    }

    public function testEnrichDocumentWithCategories(): void
    {
        $resource = new Resource(
            '',
            '123',
            '',
            '',
            ResourceLanguage::default(),
            new DataBag(['content' => ['items' => [
                ['type' => 'main', 'items' => [
                    ['id' => 'eventsCalendar-venue', 'model' => ['categories' => [['id' => '1']], 'categoriesPath' => [['id' => '2'],['id' => '1']]]],
                    ['id' => 'eventsCalendar-ticketAgency', 'model' => ['categories' => [['id' => '3']], 'categoriesPath' => [['id' => '4'],['id' => '3']]]],
                    ['id' => 'eventsCalendar-organizer', 'model' => ['categories' => [['id' => '5']], 'categoriesPath' => [['id' => '6'],['id' => '5']]]],
                ]],
            ]]]),
        );

        $doc = $this->enricher->enrichDocument(
            $resource,
            new IndexSchema2xDocument(),
            'progress-id',
        );

        $expected = new IndexSchema2xDocument();
        $expected->sp_category = ["1","3","5"];
        $expected->sp_category_path = [2,1,4,3,6,5];

        $this->assertEquals($expected, $doc, 'unexpected doc');
    }

    public function testEnrichDocumentWithoutCategories(): void
    {
        $resource = new Resource(
            '',
            '123',
            '',
            '',
            ResourceLanguage::default(),
            new DataBag([]),
        );

        $doc = $this->enricher->enrichDocument(
            $resource,
            new IndexSchema2xDocument(),
            'progress-id',
        );

        $expected = new IndexSchema2xDocument();

        $this->assertEquals($expected, $doc, 'unexpected doc');
    }
}
