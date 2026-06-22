<?php

declare(strict_types=1);

namespace Atoolo\CityGov\Test\Service\Indexer\Enricher\SiteKitSchema2x;

use Atoolo\CityGov\ChannelAttributes;
use Atoolo\CityGov\Service\Indexer\Enricher\{SiteKitSchema2x\OrganisationDocumentEnricher,
    SiteKitSchema2x\ProductDocumentEnricher};
use Atoolo\CityGov\Test\TestResourceFactory;
use Atoolo\Resource\DataBag;
use Atoolo\Resource\Exception\InvalidResourceException;
use Atoolo\Resource\Exception\ResourceNotFoundException;
use Atoolo\Resource\Resource;
use Atoolo\Resource\ResourceLanguage;
use Atoolo\Resource\ResourceLoader;
use Atoolo\Search\Exception\DocumentEnrichingException;
use Atoolo\Search\Service\Indexer\IndexSchema2xDocument;
use Atoolo\Search\Service\Indexer\SolrIndexService;
use Atoolo\Search\Service\Indexer\SolrIndexUpdater;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\Rule\InvokedCount;
use PHPUnit\Framework\TestCase;
use Solarium\Core\Query\DocumentInterface;

#[CoversClass(ProductDocumentEnricher::class)]
class ProductDocumentEnricherTest extends TestCase
{
    private SolrIndexService $solrIndexService;

    private SolrIndexUpdater $solrIndexUpdater;

    private ResourceLoader $resourceLoader;

    private OrganisationDocumentEnricher $organisationEnricher;

    private ProductDocumentEnricher $productEnricher;
    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $doc = $this->createStub(IndexSchema2xDocument::class);
        $this->solrIndexUpdater = $this->createMock(SolrIndexUpdater::class);
        $this->solrIndexUpdater->method('createDocument')
            ->willReturn($doc);
        $updateResult = $this->createStub(\Solarium\QueryType\Update\Result::class);
        $this->solrIndexUpdater->method('update')
            ->willReturn($updateResult);

        $this->solrIndexService = $this->createMock(SolrIndexService::class);
        $this->solrIndexService->method('updater')->willReturn($this->solrIndexUpdater);

        $resource  = new Resource(
            'de_DE',
            'id1',
            'resource name',
            'citygovProduct',
            ResourceLanguage::of('de_DE'),
            new DataBag([]),
        );
        $this->resourceLoader = $this->createStub(
            ResourceLoader::class,
        );
        $this->resourceLoader->expects($this->any())
            ->method('load')
            ->willReturnCallback(function ($location) use ($resource) {
                if ($location->location === 'throwException') {
                    throw new InvalidResourceException(
                        $location,
                        'throw for test',
                    );
                }
                if ($location->location === '/orga.php') {
                    return $resource;
                }
                throw new ResourceNotFoundException($location);
            });

        $this->organisationEnricher = $this->createStub(
            OrganisationDocumentEnricher::class,
        );
        $this->organisationEnricher->expects($this->any())
            ->method('enrichOrganisationPath')
            ->willReturnCallback(function ($resource, $doc) {
                $doc->sp_organisation = 12;
                return $doc;
            });

        $this->productEnricher = new ProductDocumentEnricher(
            new ChannelAttributes(false),
            $this->solrIndexService,
            $this->resourceLoader,
            $this->organisationEnricher,
        );

    }

    /**
     * @throws Exception
     */
    public function testCleanup(): void
    {
        $resourceLoader = $this->createMock(
            ResourceLoader::class,
        );
        $organisationEnricher = $this->createStub(
            OrganisationDocumentEnricher::class,
        );
        $resourceLoader->expects($this->once())
            ->method('cleanup');

        $enricher = new ProductDocumentEnricher(
            new ChannelAttributes(false),
            $this->solrIndexService,
            $resourceLoader,
            $organisationEnricher,
        );
        $enricher->cleanup();
    }

    public function testObjectType(): void
    {
        $doc = $this->enrichDocument([
            'objectType' => 'content',
        ]);

        $this->assertEquals(
            [],
            $doc->getFields(),
            'document should be empty',
        );
    }

    public function testKeywords(): void
    {
        $doc = $this->enrichDocument([
            'objectType' => 'citygovProduct',
            'metadata' => [
                'citygovProduct' => [
                    'synonymList' => [
                        'Synonym6',
                        'Synonym7',
                    ],
                ],
            ],
        ]);

        $this->assertEquals(
            [
                'Synonym6',
                'Synonym7',
            ],
            $doc->keywords,
            'unexpected keywords',
        );
    }

    public function testStartletter(): void
    {
        $doc = $this->enrichDocument([
            'objectType' => 'citygovProduct',
            'metadata' => [
                'citygovProduct' => [
                    'name' => 'ProductName',
                ],
            ],
        ]);

        $this->assertEquals(
            'P',
            $doc->sp_citygov_startletter,
            'unexpected startletter',
        );
    }

    public function testSortvalue(): void
    {
        $doc = $this->enrichDocument([
            'objectType' => 'citygovProduct',
            'metadata' => [
                'citygovProduct' => [
                    'name' => 'ProductName',
                ],
            ],
        ]);

        $this->assertEquals(
            'ProductName',
            $doc->sp_sortvalue,
            'unexpected sortvalue',
        );
    }

    public function testOrganisation(): void
    {
        $doc = $this->enrichDocument([
            'objectType' => 'citygovProduct',
            'metadata' => [
                'citygovProduct' => [
                    'responsibilityList' => [
                        'items' => [
                            [
                                'primary' => true,
                                'organisation' => [
                                    'url' => '/orga.php',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertEquals(
            '12',
            $doc->sp_organisation,
            'unexpected organisation',
        );
    }

    public function testOrganisationWithoutPrimary(): void
    {
        $doc = $this->enrichDocument([
            'objectType' => 'citygovProduct',
            'metadata' => [
                'citygovProduct' => [
                    'responsibilityList' => [
                        'items' => [
                            [
                                'organisation' => [
                                    'url' => '/orga.php',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertArrayNotHasKey(
            'sp_organisation',
            $doc->getFields(),
            'unexpected organisation',
        );
    }

    public function testOrganisationWithoutUrl(): void
    {
        $doc = $this->enrichDocument([
            'objectType' => 'citygovProduct',
            'metadata' => [
                'citygovProduct' => [
                    'responsibilityList' => [
                        'items' => [
                            [
                                'primary' => true,
                                'organisation' => [
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertArrayNotHasKey(
            'sp_organisation',
            $doc->getFields(),
            'unexpected organisation',
        );
    }

    public function testOrganisationWithException(): void
    {
        $this->expectException(DocumentEnrichingException::class);
        $this->enrichDocument([
            'objectType' => 'citygovProduct',
            'metadata' => [
                'citygovProduct' => [
                    'responsibilityList' => [
                        'items' => [
                            [
                                'primary' => true,
                                'organisation' => [
                                    'url' => 'throwException',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function testContentTypeOnlineService(): void
    {
        $doc = $this->enrichDocument([
            'objectType' => 'citygovProduct',
            'metadata' => [
                'citygovProduct' => [
                    'onlineServices' => [
                        'serviceList' => [
                            [
                                'dummy' => 'value',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertEquals(
            ['citygovOnlineService'],
            $doc->sp_contenttype,
            'unexpected contenttype',
        );
    }

    public function testLeikaKeys(): void
    {
        $doc = $this->enrichDocument([
            'objectType' => 'citygovProduct',
            'metadata' => [
                'citygovProduct' => [
                    'leikaKeys' => [
                        'leika1',
                        'leika2',
                    ],
                ],
            ],
        ]);

        /** @var array{sp_meta_string_leikanumber: string[]} $fields */
        $fields = $doc->getFields();

        $this->assertEquals(
            [
                'leika1',
                'leika2',
            ],
            $fields['sp_meta_string_leikanumber'],
            'unexpected leikaKeys',
        );
    }

    public function testRichTextContent(): void
    {
        $doc = $this->enrichDocument([
            'objectType' => 'citygovProduct',
            'metadata' => [
                'citygovProduct' => [
                    'content' => [
                        "info" => [
                            "modelType" => "citygov.contentBlock",
                            "id" => "info",
                            "contentList" => [[
                                "modelType" => "content.text",
                                "richText" => [
                                    "normalized" => true,
                                    "modelType" => "html.richText",
                                    "text" =>
                                        "<p><span>Information</span></p>",
                                ],
                            ]],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertEquals(
            'Information',
            $doc->content,
            'unexpected content',
        );
    }

    /**
     * @throws Exception
     */
    public function testAddNonOfTwoAlternativeDocument(): void
    {
        $this->solrIndexUpdater->expects($this->never())
            ->method('addDocument');
        $this->solrIndexUpdater->expects($this->never())
            ->method('update');

        $data = [
            'objectType' => 'citygovProduct',
            'metadata' => [
                'citygovProduct' => [
                    'name' => 'productName',
                    'alternativeNameList' => [
                        'second name',
                        'third name',
                    ],
                ],
            ],
        ];
        $this->enrichDocument($data);
    }


    /**
     * @throws Exception
     */
    public function testAddTwoOfTwoAlternativeDocument(): void
    {
        $this->solrIndexUpdater->expects(new InvokedCount(2))
            ->method('addDocument');
        $this->solrIndexUpdater->expects($this->once())
            ->method('update');

        $data = [
            'objectType' => 'citygovProduct',
            'metadata' => [
                'citygovProduct' => [
                    'name' => 'productName',
                    'alternativeNameList' => [
                        'second name',
                        'third name',
                    ],
                ],
            ],
        ];
        $enricher = new ProductDocumentEnricher(
            new ChannelAttributes(true),
            $this->solrIndexService,
            $this->resourceLoader,
            $this->organisationEnricher,
        );
        $doc = new IndexSchema2xDocument();
        $resource = TestResourceFactory::create($data);

        $enricher->enrichDocument($resource, $doc, '');
        $this->testCleanup();
    }


    /**
     * @throws Exception
     */
    public function testAddNOAlternativeDocument(): void
    {
        $this->solrIndexUpdater->expects($this->never())
            ->method('addDocument');
        $this->solrIndexUpdater->expects($this->never())
            ->method('update');

        $data = [
            'objectType' => 'citygovProduct',
            'metadata' => [
                'citygovProduct' => [
                    'name' => 'productName',
                ],
            ],
        ];
        $enricher = new ProductDocumentEnricher(
            new ChannelAttributes(true),
            $this->solrIndexService,
            $this->resourceLoader,
            $this->organisationEnricher,
        );
        $doc = new IndexSchema2xDocument();
        $resource = TestResourceFactory::create($data);

        $enricher->enrichDocument($resource, $doc, '');
        $this->testCleanup();
    }


    private function enrichDocument(
        array $data,
    ): DocumentInterface {
        $resource = TestResourceFactory::create($data);
        $doc = new IndexSchema2xDocument();
        return $this->productEnricher->enrichDocument($resource, $doc, '');
    }
}
