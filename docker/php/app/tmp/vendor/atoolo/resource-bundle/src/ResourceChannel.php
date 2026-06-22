<?php

declare(strict_types=1);

namespace Atoolo\Resource;

/**
 * Resources are aggregated by the IES (Sitepark's content management system)
 * into different channels. This can be the live channel for the
 * website, but also a preview or intranet channel, for example.
 *
 * These channels have certain properties that are mapped in this class.
 *
 *@codeCoverageIgnore
 */
class ResourceChannel
{
    /**
     * @param string[] $translationLocales
     */
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $anchor,
        public readonly string $serverName,
        public readonly bool $isPreview,
        public readonly string $nature,
        public readonly string $locale,
        public readonly string $baseDir,
        public readonly string $resourceDir,
        public readonly string $configDir,
        public readonly string $searchIndex,
        public readonly array $translationLocales,
        public readonly DataBag $attributes,
        public readonly ResourceTenant $tenant,
    ) {}
}
