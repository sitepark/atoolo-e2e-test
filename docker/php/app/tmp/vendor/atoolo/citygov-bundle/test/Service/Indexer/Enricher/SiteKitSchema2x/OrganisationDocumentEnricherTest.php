<?php

declare(strict_types=1);

namespace Atoolo\CityGov\Test\Service\Indexer\Enricher\SiteKitSchema2x;

use Atoolo\CityGov\ChannelAttributes;
use Atoolo\CityGov\Service\Indexer\Enricher\{SiteKitSchema2x\OrganisationDocumentEnricher};
use Atoolo\CityGov\Test\TestResourceFactory;
use Atoolo\Resource\Loader\SiteKitResourceHierarchyLoader;
use Atoolo\Resource\ResourceLocation;
use Atoolo\Search\Exception\DocumentEnrichingException;
use Atoolo\Search\Service\Indexer\IndexSchema2xDocument;
use Atoolo\Search\Service\Indexer\SolrIndexService;
use Atoolo\Search\Service\Indexer\SolrIndexUpdater;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvokedCount;
use PHPUnit\Framework\TestCase;
use Solarium\QueryType\Update\Result;

#[CoversClass(OrganisationDocumentEnricher::class)]
class OrganisationDocumentEnricherTest extends TestCase
{
    private SolrIndexService&MockObject $solrIndexService;

    private SolrIndexUpdater&MockObject $solrIndexUpdater;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $doc = $this->createStub(IndexSchema2xDocument::class);
        $this->solrIndexUpdater = $this->createMock(SolrIndexUpdater::class);
        $this->solrIndexUpdater->method('addDocument');
        $this->solrIndexUpdater->method('createDocument')
            ->willReturn($doc);
        $updateResult = $this->createStub(Result::class);
        $this->solrIndexUpdater->method('update')
            ->willReturn($updateResult);

        $this->solrIndexService = $this->createMock(SolrIndexService::class);
        $this->solrIndexService->method('updater')->willReturn($this->solrIndexUpdater);
    }

    /**
     * @throws Exception
     */
    public function testCleanup(): void
    {
        $hierarchyLoader = $this->createMock(
            SiteKitResourceHierarchyLoader::class,
        );
        $hierarchyLoader->expects($this->once())
            ->method('cleanup');

        $enricher = new OrganisationDocumentEnricher(
            new ChannelAttributes(false),
            $this->solrIndexService,
            $hierarchyLoader,
        );
        $enricher->cleanup();
    }

    /**
     * @throws Exception
     */
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

    /**
     * @throws Exception
     */
    public function testSynonymList(): void
    {
        $doc = $this->enrichDocument([
            'objectType' => 'citygovOrganisation',
            'metadata' => [
                'citygovOrganisation' => [
                    'synonymList' => ['blue', 'red'],
                ],
            ],
        ]);

        $this->assertEquals(
            ['blue', 'red'],
            $doc->keywords,
            'unexpected synonyms as keywords',
        );
    }

    /**
     * @throws Exception
     */
    public function testStartLetter(): void
    {
        $doc = $this->enrichDocument([
            'objectType' => 'citygovOrganisation',
            'metadata' => [
                'citygovOrganisation' => [
                    'name' => 'Orga',
                ],
            ],
        ]);

        $this->assertEquals(
            'O',
            $doc->sp_citygov_startletter,
            'unexpected startletter',
        );
    }

    /**
     * @throws Exception
     */
    public function testToken(): void
    {
        $doc = $this->enrichDocument([
            'objectType' => 'citygovOrganisation',
            'metadata' => [
                'citygovOrganisation' => [
                    'token' => '123',
                ],
            ],
        ]);

        $this->assertEquals(
            ['123'],
            $doc->sp_citygov_organisationtoken,
            'unexpected token',
        );
    }

    public function testTokenInContent(): void
    {
        $doc = $this->enrichDocument([
            'objectType' => 'citygovOrganisation',
            'metadata' => [
                'citygovOrganisation' => [
                    'token' => '123',
                ],
            ],
        ]);

        $this->assertEquals(
            '123',
            $doc->content,
            'unexpected token',
        );
    }


    /**
     * @throws Exception
     */
    public function testSortvalue(): void
    {
        $doc = $this->enrichDocument([
            'objectType' => 'citygovOrganisation',
            'metadata' => [
                'citygovOrganisation' => [
                    'name' => 'Orga',
                ],
            ],
        ]);

        $this->assertEquals(
            'Orga',
            $doc->sp_sortvalue,
            'unexpected sortvalue',
        );
    }

    /**
     * @throws Exception
     */
    public function testOrgaId(): void
    {
        $doc = $this->enrichOrganisationPath(
            [
                'id' => '123',
            ],
        );

        $this->assertEquals(
            123,
            $doc->sp_organisation,
            'unexpected id',
        );
    }

    /**
     * @throws Exception
     */
    public function testOrgaPath(): void
    {
        $doc = $this->enrichOrganisationPath(
            [
                'base' => [
                    'trees' => [
                        'citygovOrganisation' => [
                            'parents' => [
                                '12' => [
                                    'url' => '/12.php',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        );

        $this->assertEquals(
            [12, 123],
            $doc->sp_organisation_path,
            'unexpected path',
        );
    }

    public function testOrgaPathWithException(): void
    {
        $hierarchyLoader = $this->createStub(
            SiteKitResourceHierarchyLoader::class,
        );
        $resource = TestResourceFactory::create([
            'url' => '/12.php',
            'id' => '12',
            'name' => '12',
            'objectType' => 'citygovOrganisation',
        ]);

        $hierarchyLoader
            ->method('loadPrimaryPath')
            ->willThrowException(new DocumentEnrichingException(
                ResourceLocation::of('test'),
                'test',
            ));

        $enricher = new OrganisationDocumentEnricher(
            new ChannelAttributes(false),
            $this->solrIndexService,
            $hierarchyLoader,
        );
        $doc = $this->createMock(IndexSchema2xDocument::class);

        $this->expectException(DocumentEnrichingException::class);
        $enricher->enrichOrganisationPath($resource, $doc);
    }


    /**
     * @throws Exception
     */
    public function testAddAlternativeDocument(): void
    {
        $this->solrIndexUpdater->expects(new InvokedCount(2))
            ->method('addDocument');
        $this->solrIndexUpdater->expects($this->once())
            ->method('update');

        $this->enrichDocument([
            'objectType' => 'citygovOrganisation',
            'metadata' => [
                'citygovOrganisation' => [
                    'name' => 'Name of orgnization',
                    'alternativeNameList' => [
                        'Alternative name',
                        'Second name',
                    ],
                ],
            ],
        ]);
        $this->testCleanup();
    }

    /**
     * @throws Exception
     */
    private function enrichDocument(
        array $data,
    ): IndexSchema2xDocument {
        $hierarchyLoader = $this->createStub(
            SiteKitResourceHierarchyLoader::class,
        );
        $enricher = new OrganisationDocumentEnricher(
            new ChannelAttributes(true),
            $this->solrIndexService,
            $hierarchyLoader,
        );
        $doc = $this->createMock(IndexSchema2xDocument::class);

        $resource = TestResourceFactory::create($data);

        return $enricher->enrichDocument($resource, $doc, '');
    }

    /**
     * @throws Exception
     */
    private function enrichOrganisationPath(
        array $data,
    ): IndexSchema2xDocument {
        $hierarchyLoader = $this->createStub(
            SiteKitResourceHierarchyLoader::class,
        );
        $resource12 = TestResourceFactory::create(array_merge([
            'url' => '/12.php',
            'id' => '12',
            'name' => '12',
            'objectType' => 'citygovOrganisation',
        ], $data));
        $resource123 = TestResourceFactory::create(array_merge([
            'url' => '/123.php',
            'id' => '123',
            'name' => '123',
            'objectType' => 'citygovOrganisation',
        ], $data));

        $hierarchyLoader
            ->method('loadPrimaryPath')
            ->willReturn([$resource12, $resource123]);

        $enricher = new OrganisationDocumentEnricher(
            new ChannelAttributes(false),
            $this->solrIndexService,
            $hierarchyLoader,
        );
        $doc = $this->createMock(IndexSchema2xDocument::class);
        $enricher->enrichOrganisationPath($resource123, $doc);
        return $doc;
    }
}
