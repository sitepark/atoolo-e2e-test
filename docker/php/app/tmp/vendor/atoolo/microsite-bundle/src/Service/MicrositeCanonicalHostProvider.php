<?php

declare(strict_types=1);

namespace Atoolo\Microsite\Service;

use Atoolo\Microsite\Environment\MicrositeContext;
use Atoolo\Security\Service\CanonicalHostProvider;

class MicrositeCanonicalHostProvider implements CanonicalHostProvider
{
    public function __construct(
        private readonly ?MicrositeContext $micrositeContext,
    ) {}

    public function getCanonicalHost(): ?string
    {
        if ($this->micrositeContext === null) {
            return null;
        }
        return $this->micrositeContext->micrositeHost;
    }
}
