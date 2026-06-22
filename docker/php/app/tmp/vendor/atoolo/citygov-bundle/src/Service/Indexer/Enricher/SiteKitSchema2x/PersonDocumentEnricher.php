<?php

declare(strict_types=1);

namespace Atoolo\CityGov\Service\Indexer\Enricher\SiteKitSchema2x;

use Atoolo\Resource\Resource;
use Atoolo\Resource\ResourceLoader;
use Atoolo\Resource\ResourceLocation;
use Atoolo\Search\Exception\DocumentEnrichingException;
use Atoolo\Search\Service\Indexer\DocumentEnricher;
use Atoolo\Search\Service\Indexer\IndexDocument;
use Atoolo\Search\Service\Indexer\IndexSchema2xDocument;
use Exception;

/**
 * TODO person.google.noIndex -> https://gitlab.sitepark.com/sitekit/citygov-php/-/blob/develop/php/SP/CityGov/Component/Initialization.php?ref_type=heads#L218
 *
 * @phpstan-type Membership array{
 *      primary?: bool,
 *      organisation?:array{
 *          url?:string
 *      }
 *  }
 * @phpstan-type Competence array{
 *       primary?: bool,
 *       product?:array{
 *           url?:string
 *       }
 *   }
 *
 * @implements DocumentEnricher<IndexSchema2xDocument>
 */
class PersonDocumentEnricher implements DocumentEnricher
{
    public function __construct(
        private readonly ResourceLoader $resourceLoader,
        private readonly OrganisationDocumentEnricher $organisationEnricher,
    ) {}

    public function cleanup(): void
    {
        $this->resourceLoader->cleanup();
    }

    /**
     * @throws DocumentEnrichingException
     */
    public function enrichDocument(
        Resource $resource,
        IndexDocument $doc,
        string $processId,
    ): IndexDocument {

        if ($resource->objectType !== 'citygovPerson') {
            return $doc;
        }

        return $this->enrichDocumentForPerson($resource, $doc);
    }

    /**
     * @template E of IndexSchema2xDocument
     * @param E $doc
     * @return E
     */
    private function enrichDocumentForPerson(
        Resource $resource,
        IndexDocument $doc,
    ): IndexDocument {

        $firstname = $resource->data->getString(
            'metadata.citygovPerson.firstname',
        );
        $lastname = $resource->data->getString(
            'metadata.citygovPerson.lastname',
        );

        $doc->sp_citygov_firstname = $firstname;
        $doc->sp_citygov_lastname = $lastname;
        /*
         * Sort value with two search criteria. `aaa` ensures that, for example,
         * `SchmittaaaOtto` and `SchmittmannaaaHans` are sorted so that
         * `SchmittaaaOtto` is sorted first.
         */
        $sortName = str_replace(
            ["ä", "ö", "ü", "Ä", "Ö", "Ü"],
            ["ae", "oe", "ue", "Ae", "Oe", "Ue"],
            $lastname . 'aaa' . $firstname,
        );
        $doc->sp_sortvalue = $sortName;
        $doc->sp_citygov_startletter = mb_substr($sortName, 0, 1);
        $doc->sp_startletter = mb_substr($sortName, 0, 1);

        try {
            $doc = $this->enrichPersonOrganisations($resource, $doc);
        } catch (Exception $e) {
            throw new DocumentEnrichingException(
                $resource->toLocation(),
                'Unable to enrich organisation for person',
                0,
                $e,
            );
        }

        try {
            $doc = $this->enrichPersonProducts($resource, $doc);
        } catch (Exception $e) {
            throw new DocumentEnrichingException(
                $resource->toLocation(),
                'Unable to enrich products for person',
                0,
                $e,
            );
        }

        $functionName = $resource->data->getString(
            'metadata.citygovPerson.function.name',
        );
        $functionAppendix = $resource->data->getString(
            'metadata.citygovPerson.function.appendix',
        );

        $doc->sp_citygov_function = trim(
            $functionName . ' ' . $functionAppendix,
        );

        $content = ($doc->content ?? '') . ' ' . $doc->sp_citygov_function;
        $doc->content = trim($content);

        return $doc;
    }

    /**
     * @template E of IndexSchema2xDocument
     * @param E $doc
     * @return E
     */
    private function enrichPersonOrganisations(
        Resource $resource,
        IndexDocument $doc,
    ): IndexDocument {
        /** @var Membership[] $membershipList */
        $membershipList = $resource->data->getArray(
            'metadata.citygovPerson.membershipList.items',
        );
        /** @var array<array<string>> $organisationNameMergeList */
        $organisationNameMergeList = [];
        /** @var string[] $organisationTokenList */
        $organisationTokenList = [];

        foreach ($membershipList as $membership) {
            $organisationLocation = $membership['organisation']['url'] ?? null;
            if ($organisationLocation === null) {
                continue;
            }

            $organisationResource = $this->resourceLoader->load(
                ResourceLocation::of($organisationLocation, $resource->lang),
            );

            $organisationNameMergeList[] = [
                $organisationResource->data->getString(
                    'metadata.citygovOrganisation.name',
                ),
            ];

            $token = $organisationResource->data->getString(
                'metadata.citygovOrganisation.token',
            );
            if (!empty($token)) {
                $organisationTokenList[] = str_replace('.', ' ', $token);
            }

            $synonymList = $organisationResource->data->getArray(
                'metadata.citygovOrganisation.synonymList',
            );
            $organisationNameMergeList[] = $synonymList;

            if (($membership['primary'] ?? false) === true) {
                $this->organisationEnricher->enrichOrganisationPath(
                    $organisationResource,
                    $doc,
                );
            }
        }

        /** @var string[] $organisationNameList */
        $organisationNameList = array_merge([], ...$organisationNameMergeList);

        $doc->sp_citygov_organisation = $organisationNameList;
        $doc->sp_citygov_organisationtoken = $organisationTokenList;

        return $doc;
    }

    /**
     * @template E of IndexSchema2xDocument
     * @param E $doc
     * @return E
     */
    private function enrichPersonProducts(
        Resource $resource,
        IndexDocument $doc,
    ): IndexDocument {

        /** @var Competence[] $competenceList */
        $competenceList = $resource->data->getArray(
            'metadata.citygovPerson.competenceList.items',
        );
        $productNameMergeList = [];

        foreach ($competenceList as $competence) {
            $productLocation = $competence['product']['url'] ?? null;
            if ($productLocation === null) {
                continue;
            }
            $productResource = $this->resourceLoader->load(
                ResourceLocation::of(
                    $productLocation,
                    $resource->lang,
                ),
            );

            $productNameMergeList[] = [
                $productResource->data->getString(
                    'metadata.citygovProduct.name',
                ),
            ];

            $synonymList = $productResource->data->getArray(
                'metadata.citygovProduct.synonymList',
            );
            $productNameMergeList[] = $synonymList;
        }

        /** @var string[] $productNameList */
        $productNameList = array_merge([], ...$productNameMergeList);

        $doc->sp_citygov_product = $productNameList;

        return $doc;
    }
}
