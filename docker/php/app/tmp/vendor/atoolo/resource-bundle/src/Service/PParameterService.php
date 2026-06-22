<?php

declare(strict_types=1);

namespace Atoolo\Resource\Service;

use Atoolo\Resource\ResourceHierarchyLoader;
use Atoolo\Resource\ResourceLoader;
use Atoolo\Resource\ResourceLocation;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * the P-parameter is used to specify the path of the resource from the root via a
 * comma-separated list of IDs. This may be necessary if a secondary navigation path
 * is to be used.
 *
 * A special feature is that it is desired for certain use cases that
 * an article is to be mounted in a foreign navigation node, e.g. search hits below
 * the search page if the URL is called up via the search result.
 * This form of the P-parameter is also protected by a signature so that any article
 * cannot be inserted below any article by manipulating the P-parameter.
 */
#[AsAlias(id: 'atoolo_resource.p_parameter_service')]
class PParameterService
{
    public function __construct(
        #[Autowire(service: 'atoolo_resource.cached_resource_loader')]
        private readonly ResourceLoader $resourceLoader,
        #[Autowire(service: 'atoolo_resource.navigation_hierarchy_loader')]
        private readonly ResourceHierarchyLoader $navigationLoader,
        #[Autowire(env: 'APP_SECRET')]
        private readonly string $secret,
    ) {}

    public function getPParameterForForeignParent(
        ResourceLocation $foreignParent,
        ResourceLocation $resourceLocation,
    ): string {
        $resource = $this->resourceLoader->load($resourceLocation);
        $path = $this->navigationLoader->loadPrimaryPath($foreignParent);

        $pParameter = [];
        for ($i = 0; $i < count($path) - 1; $i++) {
            $pParameter[] = $path[$i]->id;
        }
        $pParameter[] = $path[count($path) - 1]->location;
        $pParameter[] = $resource->id;

        $p = implode(',', $pParameter);
        $sig = $this->signPathParam($p);
        return 'sig:' . $sig . ',' . $p;
    }

    public function signPathParam(string $path): string
    {
        $length = 16;
        $hmac = hash_hmac('sha256', $path, $this->secret, true);
        return rtrim(strtr(substr(base64_encode($hmac), 0, $length), '+/', '-_'), '=');
    }
}
