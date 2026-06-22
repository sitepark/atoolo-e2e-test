<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Service;

use Atoolo\Resource\ResourceChannel;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class IesUrlResolver
{
    public function __construct(
        #[Autowire(service: 'atoolo_resource.resource_channel')]
        private readonly ResourceChannel $resourceChannel,
        #[Autowire('%env(default::IES_SCHEME)%')]
        private readonly ?string $iesScheme,
        #[Autowire('%env(default::IES_HOST)%')]
        private readonly ?string $iesHost,
        #[Autowire('%env(default::IES_PORT)%')]
        private readonly ?string $iesPort,
    ) {}

    public function getBaseUrl(): string
    {
        if ($this->iesHost) {
            $scheme = $this->iesScheme ?: 'https';
            $port = $this->iesPort ? ':' . $this->iesPort : '';
            return sprintf('%s://%s%s', $scheme, $this->iesHost, $port);
        }

        return 'https://' . $this->resourceChannel->tenant->host;
    }
}
