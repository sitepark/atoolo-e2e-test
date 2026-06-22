<?php

declare(strict_types=1);

namespace Atoolo\CityGov\Test\Service\Indexer\Enricher\SiteKitSchema2x;

use Atoolo\CityGov\Service\Indexer\Enricher\{
    SiteKitSchema2x\ContactPointDocumentEnricher
};
use Atoolo\CityGov\Test\TestResourceFactory;
use Atoolo\Search\Service\Indexer\IndexSchema2xDocument;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

#[CoversClass(ContactPointDocumentEnricher::class)]
class ContactPointDocumentEnricherTest extends TestCase
{
    public function testCleanup(): void
    {
        $this->expectNotToPerformAssertions();
        $enricher = new ContactPointDocumentEnricher();
        $enricher->cleanup();
    }
    /**
     * @throws Exception
     */
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
    public function testPhone(): void
    {
        $doc = $this->enrichDocument([
            'objectType' => 'citygovOrganisation',
            'metadata' => [
                'contactPoint' => [
                    'contactData' => [
                        'phoneList' => [
                            [
                                'phone' => [
                                    'nationalNumber' => '123',
                                ],
                            ],
                            [
                                'phone' => [
                                    'nationalNumber' => '456',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertEquals(
            ['123', '456'],
            $doc->sp_citygov_phone,
            'unexpected phones',
        );
    }

    /**
     * @throws Exception
     */
    public function testEmail(): void
    {
        $doc = $this->enrichDocument([
            'objectType' => 'citygovPerson',
            'metadata' => [
                'contactPoint' => [
                    'contactData' => [
                        'emailList' => [
                            [
                                'email' => 'a@b.de',
                            ],
                            [
                                'email' => 'c@d.de',
                            ],
                        ],
                    ],
                ],
            ],
        ]);

        $this->assertEquals(
            ['a@b.de', 'c@d.de'],
            $doc->sp_citygov_email,
            'unexpected emails',
        );
    }

    /**
     * @throws Exception
     */
    public function testAddress(): void
    {
        $doc = $this->enrichDocument([
            'objectType' => 'citygovOrganisation',
            'metadata' => [
                'contactPoint' => [
                    'addressData' => [
                        'buildingName' => 'Building',
                        'street' => 'Street',
                        'housenumber' => '10',
                    ],
                ],
            ],
        ]);

        $this->assertEquals(
            'Building Street 10',
            $doc->sp_citygov_address,
            'unexpected address',
        );
    }

    /**
     * @throws Exception
     */
    private function enrichDocument(
        array $data,
    ): IndexSchema2xDocument {
        $enricher = new ContactPointDocumentEnricher();
        $doc = $this->createMock(IndexSchema2xDocument::class);

        $resource = TestResourceFactory::create($data);

        return $enricher->enrichDocument($resource, $doc, '');
    }
}
