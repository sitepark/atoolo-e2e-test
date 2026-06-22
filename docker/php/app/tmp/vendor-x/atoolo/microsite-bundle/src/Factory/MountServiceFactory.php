<?php

declare(strict_types=1);

namespace Atoolo\Microsite\Factory;

use Atoolo\Microsite\Environment\MicrositeContext;
use Atoolo\Microsite\Service\MountService;
use Atoolo\Microsite\Service\Platform;
use Atoolo\Resource\ResourceLoader;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class MountServiceFactory
{
    public function __construct(
        private readonly ?MicrositeContext $micrositeContext,
        #[Autowire(service: 'atoolo_resource.cached_resource_loader')]
        private readonly ResourceLoader $resourceLoader,
        private readonly Platform $platform,
        private readonly LoggerInterface $logger,
    ) {}

    public function create(): ?MountService
    {
        if ($this->micrositeContext === null) {
            return null;
        }
        return new MountService($this->micrositeContext, $this->resourceLoader, $this->platform, $this->logger);
    }
}
