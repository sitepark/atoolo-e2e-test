<?php

declare(strict_types=1);

namespace Atoolo\CityGov\Service\GraphQL\Factory;

use Atoolo\CityGov\Service\GraphQL\Types\OnlineService;
use Atoolo\CityGov\Service\GraphQL\Types\OnlineServiceFeature;
use Atoolo\GraphQL\Search\Factory\LinkFactory;
use Atoolo\GraphQL\Search\Factory\TeaserFeatureFactory;
use Atoolo\GraphQL\Search\Types\TeaserFeature;
use Atoolo\Resource\Resource;
use Atoolo\Resource\ResourceLoader;
use Atoolo\Resource\ResourceLocation;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class OnlineServiceFeatureFactory implements TeaserFeatureFactory, LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(
        protected ResourceLoader $resourceLoader,
        protected LinkFactory $linkFactory,
    ) {}

    /**
     * @return TeaserFeature[]
     */
    public function create(
        Resource $resource,
    ): array {
        $onlineServiceFeatures = [];
        if ($resource->data->has('metadata.citygovProduct.onlineServices')) {
            $onlineServices = [];

            /** @var array{url: string} $onlineServiceRaw */
            foreach (
                $resource->data->getArray('metadata.citygovProduct.onlineServices.serviceList.items') as $onlineServiceRaw
            ) {
                try {
                    $onlineServiceResource = $this->resourceLoader->load(
                        ResourceLocation::of($onlineServiceRaw['url']),
                    );
                    $onlineServices[] = new OnlineService(
                        $this->linkFactory->create($onlineServiceResource),
                    );
                } catch (\Throwable $th) {
                    $this->logger?->error(
                        'error while loading resource for online service',
                        [
                            'error' => $th,
                        ],
                    );
                }
            }
            $onlineServiceFeatures[] = new OnlineServiceFeature(
                null,
                $onlineServices,
            );
        }
        return $onlineServiceFeatures;
    }
}
