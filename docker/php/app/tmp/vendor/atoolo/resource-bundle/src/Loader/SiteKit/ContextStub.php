<?php

declare(strict_types=1);

namespace Atoolo\Resource\Loader\SiteKit;

/**
 * Provides the behavior of a context needed to load a SiteKit resource.
 * @codeCoverageIgnore
 */
class ContextStub
{
    public function redirectToTranslation(
        LifecylceStub $lifecylce,
        string $path,
    ): mixed {
        return null;
    }
}
