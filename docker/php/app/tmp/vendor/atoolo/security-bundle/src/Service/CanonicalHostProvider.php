<?php

declare(strict_types=1);

namespace Atoolo\Security\Service;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('atoolo_security.canonical_host_provider')]
interface CanonicalHostProvider
{
    public function getCanonicalHost(): ?string;
}
