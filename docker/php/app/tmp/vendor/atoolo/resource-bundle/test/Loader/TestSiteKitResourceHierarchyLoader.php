<?php

declare(strict_types=1);

namespace Atoolo\Resource\Test\Loader;

use Atoolo\Resource\Loader\SiteKitResourceHierarchyLoader;
use Atoolo\Resource\Resource;

class TestSiteKitResourceHierarchyLoader extends SiteKitResourceHierarchyLoader
{
    public function isRoot(Resource $resource): bool
    {
        return false;
    }
}
