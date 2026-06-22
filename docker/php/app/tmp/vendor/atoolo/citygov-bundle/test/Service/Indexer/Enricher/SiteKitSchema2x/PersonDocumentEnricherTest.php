<?php

declare(strict_types=1);

namespace Atoolo\CityGov\Test\Service\Indexer\Enricher\SiteKitSchema2x;

use Atoolo\CityGov\Service\Indexer\Enricher\{
    SiteKitSchema2x\OrganisationDocumentEnricher,
    SiteKitSchema2x\PersonDocumentEnricher
};
use Atoolo\CityGov\Test\TestResourceFactory;
use Atoolo\Resource\Exception\InvalidResourceException;
use Atoolo\Resource\Exception\ResourceNotFoundException;
use Atoolo\Resource\ResourceLoader;
use Atoolo\Search\Exception\DocumentEnrichingException;
use Atoolo\Search\Service\Indexer\IndexSchema2xDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Solarium\Core\Query\DocumentInterface;

#[CoversClass(PersonDocumentEnricher::class)]
class PersonDocumentEnricherTest extends TestCase
{
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

        $enricher = new PersonDocumentEnricher(
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

    public function testFirstname(): void
    {
        $doc = $this->enrichDocument([
            'objectType' => 'citygovPerson',
            'metadata' => [
                'citygovPerson' => [
                    'firstname' => 'Peter',
                ],
            ],
        ]);

        $this->assertEquals(
            'Peter',
            $doc->sp_citygov_firstname,
            'unexpected firstname',
        );
    }

    public function testLastname(): void
    {
        $doc = $this->enrichDocument([
            'objectType' => 'citygovPerson',
            'metadata' => [
                'citygovPerson' => [
                    'lastname' => 'Pan',
                ],
            ],
        ]);

        $this->assertEquals(
            'Pan',
            $doc->sp_citygov_lastname,
            'unexpected lastname',
        );
    }

    public function testSortvalue(): void
    {
        $doc = $this->enrichDocument([
            'objectType' => 'citygovPerson',
            'metadata' => [
                'citygovPerson' => [
                    'firstname' => 'Peter',
                    'lastname' => 'Pan',
                ],
            ],
        ]);

        $this->assertEquals(
            'PanaaaPeter',
            $doc->sp_sortvalue,
            'unexpected sortvalue',
        );
    }

    public function testStartletter(): void
    {
        $doc = $this->enrichDocument([
            'objectType' => 'citygovPerson',
            'metadata' => [
                'citygovPerson' => [
                    'lastname' => 'Pan',
                ],
            ],
        ]);

        $this->assertEquals(
            'P',
            $doc->sp_citygov_startletter,
            'unexpected startletter',
        );
    }

    public function testOrganisation(): void
    {
        $doc = $this->enrichDocument([
            'objectType' => 'citygovPerson',
            'metadata' => [
                'citygovPerson' => [
                    'membershipList' => [
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

        $this->assertEquals(
            [
                'orgaName',
                'Synonym1',
                'Synonym2',
            ],
            $doc->sp_citygov_organisation,
            'unexpected organisation',
        );
    }

    public function testOrganisationWithoutUrl(): void
    {
        $doc = $this->enrichDocument([
            'objectType' => 'citygovPerson',
            'metadata' => [
                'citygovPerson' => [
                    'membershipList' => [
                        'items' => [
                            [
                                'organisation' => [
                                    'urlx' => '/orga.php',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertEquals(
            [
            ],
            $doc->sp_citygov_organisation,
            'unexpected organisation',
        );
    }

    public function testOrganisationWithException(): void
    {
        $this->expectException(DocumentEnrichingException::class);
        $this->enrichDocument([
            'objectType' => 'citygovPerson',
            'metadata' => [
                'citygovPerson' => [
                    'membershipList' => [
                        'items' => [
                            [
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

    public function testOrganisationToken(): void
    {
        $doc = $this->enrichDocument([
            'objectType' => 'citygovPerson',
            'metadata' => [
                'citygovPerson' => [
                    'membershipList' => [
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

        $this->assertEquals(
            [
                'token A',
            ],
            $doc->sp_citygov_organisationtoken,
            'unexpected organisation',
        );
    }

    public function testOrganisationPrimary(): void
    {
        $doc = $this->enrichDocument([
            'objectType' => 'citygovPerson',
            'metadata' => [
                'citygovPerson' => [
                    'membershipList' => [
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

    public function testProduct(): void
    {
        $doc = $this->enrichDocument([
            'objectType' => 'citygovPerson',
            'metadata' => [
                'citygovPerson' => [
                    'competenceList' => [
                        'items' => [
                            [
                                'product' => [
                                    'url' => '/product.php',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertEquals(
            [
                'productName',
                'Synonym3',
                'Synonym4',
            ],
            $doc->sp_citygov_product,
            'unexpected organisation',
        );
    }

    public function testProductWithoutUrl(): void
    {
        $doc = $this->enrichDocument([
            'objectType' => 'citygovPerson',
            'metadata' => [
                'citygovPerson' => [
                    'competenceList' => [
                        'items' => [
                            [
                                'product' => [
                                    'urlx' => '/product.php',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertEquals(
            [],
            $doc->sp_citygov_product,
            'unexpected organisation',
        );
    }

    public function testProductWithException(): void
    {
        $this->expectException(DocumentEnrichingException::class);
        $this->enrichDocument([
            'objectType' => 'citygovPerson',
            'metadata' => [
                'citygovPerson' => [
                    'competenceList' => [
                        'items' => [
                            [
                                'product' => [
                                    'url' => 'throwException',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);
    }

    public function testFunction(): void
    {
        $doc = $this->enrichDocument([
            'objectType' => 'citygovPerson',
            'metadata' => [
                'citygovPerson' => [
                    'function' => [
                        'name' => 'FunctionName',
                        'appendix' => 'FunctionAppendix',
                    ],
                ],
            ],
        ]);

        $this->assertEquals(
            'FunctionName FunctionAppendix',
            $doc->sp_citygov_function,
            'unexpected function',
        );
    }

    public function testFunctionInContent(): void
    {
        $doc = $this->enrichDocument([
            'objectType' => 'citygovPerson',
            'metadata' => [
                'citygovPerson' => [
                    'function' => [
                        'name' => 'FunctionName',
                        'appendix' => 'FunctionAppendix',
                    ],
                ],
            ],
        ]);

        $this->assertEquals(
            'FunctionName FunctionAppendix',
            $doc->content,
            'unexpected function',
        );
    }

    private function enrichDocument(
        array $data,
    ): DocumentInterface {

        $resourceLoader = $this->createStub(
            ResourceLoader::class,
        );
        $orga = TestResourceFactory::create([
            'url' => '/orga.php',
            'id' => '12',
            'name' => 'orga',
            'objectType' => 'citygovOrganisation',
            'metadata' => [
                'citygovOrganisation' => [
                    'name' => 'orgaName',
                    'token' => 'token.A',
                    'synonymList' => ['Synonym1', 'Synonym2'],
                ],
            ],
        ]);
        $product = TestResourceFactory::create([
            'url' => '/product.php',
            'id' => '34',
            'name' => 'product',
            'objectType' => 'citygovProduct',
            'metadata' => [
                'citygovProduct' => [
                    'name' => 'productName',
                    'synonymList' => ['Synonym3', 'Synonym4'],
                ],
            ],
        ]);
        $resourceLoader->expects($this->any())
            ->method('load')
            ->willReturnCallback(function ($location) use ($orga, $product) {
                if ($location->location === 'throwException') {
                    throw new InvalidResourceException(
                        $location,
                        'throw for test',
                    );
                }
                if ($location->location === '/orga.php') {
                    return $orga;
                }
                if ($location->location === '/product.php') {
                    return $product;
                }
                throw new ResourceNotFoundException($location);
            });
        $organisationEnricher = $this->createStub(
            OrganisationDocumentEnricher::class,
        );
        $organisationEnricher->expects($this->any())
            ->method('enrichOrganisationPath')
            ->willReturnCallback(function ($resource, $doc) {
                $doc->sp_organisation = 12;
                return $doc;
            });
        $enricher = new PersonDocumentEnricher(
            $resourceLoader,
            $organisationEnricher,
        );
        $doc = $this->createMock(IndexSchema2xDocument::class);

        $resource = TestResourceFactory::create($data);

        return $enricher->enrichDocument($resource, $doc, '');
    }
}
