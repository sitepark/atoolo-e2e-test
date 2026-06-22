<?php

declare(strict_types=1);

namespace Atoolo\CityGov\Service\Indexer\Enricher\SiteKitSchema2x;

use Atoolo\Resource\Resource;
use Atoolo\Search\Exception\DocumentEnrichingException;
use Atoolo\Search\Service\Indexer\DocumentEnricher;
use Atoolo\Search\Service\Indexer\IndexDocument;
use Atoolo\Search\Service\Indexer\IndexSchema2xDocument;

/**
 * @phpstan-type Phone array{nationalNumber?:string}
 * @phpstan-type PhoneData array{phone:Phone}
 * @phpstan-type PhoneList array<PhoneData>
 * @phpstan-type Email array{email?:string}
 * @phpstan-type EmailList array<Email>
 * @phpstan-type ContactData array{
 *     phoneList?:PhoneList,
 *     emailList:EmailList
 * }
 * @phpstan-type AddressData array{
 *     buildingName?:string,
 *     street?:string,
 *     housenumber?:string
 * }
 * @phpstan-type ContactPoint array{
 *     contactData?:ContactData,
 *     addressData?:AddressData
 * }
 *
 * @implements DocumentEnricher<IndexSchema2xDocument>
 */
class ContactPointDocumentEnricher implements DocumentEnricher
{
    public function cleanup(): void {}

    /**
     * @throws DocumentEnrichingException
     */
    public function enrichDocument(
        Resource $resource,
        IndexDocument $doc,
        string $processId,
    ): IndexDocument {

        if (
            $resource->objectType !== 'citygovOrganisation'
            && $resource->objectType !== 'citygovPerson'
        ) {
            return $doc;
        }

        /** @var ContactPoint $contactPoint */
        $contactPoint = $resource->data->getAssociativeArray(
            'metadata.contactPoint',
        );
        return $this->enrichDocumentForContactPoint(
            $contactPoint,
            $doc,
        );
    }

    /**
     * @template E of IndexSchema2xDocument
     * @param ContactPoint $contactPoint
     * @param E $doc
     * @return E
     */
    private function enrichDocumentForContactPoint(
        array $contactPoint,
        IndexDocument $doc,
    ): IndexDocument {

        $phoneList = [];
        $phoneListData = $contactPoint['contactData']['phoneList'] ?? [];
        foreach ($phoneListData as $phoneData) {
            if (isset($phoneData['phone']['nationalNumber'])) {
                $phoneList[] = $phoneData['phone']['nationalNumber'];
            }
        }
        $doc->sp_citygov_phone = $phoneList;

        $emailList = [];
        $emailListData = $contactPoint['contactData']['emailList'] ?? [];
        foreach ($emailListData as $emailData) {
            if (isset($emailData['email'])) {
                $emailList[] = $emailData['email'];
            }
        }
        $doc->sp_citygov_email = $emailList;

        $addressSearchValue = $this->toAddressSearchValue(
            $contactPoint['addressData'] ?? [],
        );

        $doc->sp_citygov_address = $addressSearchValue;

        return $doc;
    }

    /**
     * @param AddressData $address
     * @return string
     */
    private function toAddressSearchValue(array $address): string
    {
        return trim(($address['buildingName'] ?? '') . ' '
            . ($address['street'] ?? '') . ' '
            . ($address['housenumber'] ?? ''));
    }
}
