<?php

declare(strict_types=1);

namespace Atoolo\Resource;

/**
 * In the CMS context, the term “tenant” refers to an isolated area within a
 * system that is configured specifically for a particular organization
 * or user group. Each tenant has its own data, settings and users, which
 * enables separate administration and customization.
 *
 * Resources are made available in ResourceChannels. ResourceChannels are assigned
 * to a tenant within the CMS system.
 *
 * In older APIs of the CMS system, the term “client” was also used for this.
 *
 * @codeCoverageIgnore
 */
class ResourceTenant
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $anchor,
        public readonly string $host,
        public readonly DataBag $attributes,
    ) {}
}
