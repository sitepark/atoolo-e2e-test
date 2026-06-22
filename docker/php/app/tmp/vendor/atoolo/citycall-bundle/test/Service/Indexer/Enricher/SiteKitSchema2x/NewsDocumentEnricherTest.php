<?php

declare(strict_types=1);

namespace Atoolo\CityCall\Test\Service\Indexer\Enricher\SiteKitSchema2x;

use Atoolo\CityCall\Service\Indexer\Enricher\{
    SiteKitSchema2x\NewsDocumentEnricher
};
use Atoolo\CityCall\Test\TestResourceFactory;
use Atoolo\Search\Service\Indexer\IndexSchema2xDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(NewsDocumentEnricher::class)]
class NewsDocumentEnricherTest extends TestCase
{
    public function testIsAdHocActive(): void
    {
        $doc = $this->enrichDocument([
            'objectType' => 'citycall-news',
            'metadata' => [
                'isAdHocActive' => true,
            ],
        ]);

        /** @var array{sp_meta_string_leikanumber: bool} $fields */
        $fields = $doc->getFields();

        $this->assertTrue(
            $fields['sp_meta_bool_citycall_isadhoc'],
            'unexpected isadhoc',
        );
    }

    public function testWithNonNewsObjectType(): void
    {
        $doc = $this->enrichDocument([
            'objectType' => 'otherType',
            'metadata' => [
                'isAdHocActive' => true,
            ],
        ]);

        /** @var array{sp_meta_string_leikanumber: bool} $fields */
        $fields = $doc->getFields();

        $this->assertEmpty(
            $fields,
            'document should be empty',
        );
    }

    public function testCleanup(): void
    {
        $this->expectNotToPerformAssertions();
        $enricher = new NewsDocumentEnricher();
        $enricher->cleanup();
    }

    private function enrichDocument(
        array $data,
    ): IndexSchema2xDocument {
        $enricher = new NewsDocumentEnricher();
        $doc = new IndexSchema2xDocument();

        $resource = TestResourceFactory::create($data);

        return $enricher->enrichDocument($resource, $doc, '');
    }
}
