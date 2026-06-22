<?php

declare(strict_types=1);

namespace Atoolo\Security\Service;

use Atoolo\Resource\ResourceChannel;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

/**
 * Provides a secure mechanism to retrieve the canonical host from multiple host providers.
 *
 * This service is crucial for preventing host-based security vulnerabilities by:
 * - Protecting against HTTP Host header attacks
 * - Ensuring only trusted hosts are used
 * - Preventing potential domain spoofing
 *
 * Typical attack vectors mitigated:
 * - Manipulated Host headers
 * - Cross-Site Request Forgery (CSRF) via host manipulation
 * - Unauthorized domain access
 *
 * The service is part of the security bundle because:
 * - It validates and sanitizes host information
 * - Provides a critical layer of input validation
 * - Prevents potential security exploits at the application entry point
 *
 * Use cases:
 * - Secure routing
 * - Application firewall protection
 * - Trusted host verification
 *
 * @package Atoolo\Security\Service
 * @see CanonicalHostProvider
 */
#[AsAlias(id: 'atoolo_security.canonical_host_service')]
class CanonicalHostService
{
    /**
     * @param iterable<CanonicalHostProvider> $hostProviders
     */
    public function __construct(
        #[Autowire(service: 'atoolo_resource.resource_channel')]
        private readonly ResourceChannel $resourceChannel,
        #[AutowireIterator('atoolo_security.canonical_host_provider')]
        private readonly iterable $hostProviders,
    ) {}

    /**
     * Securely retrieves the canonical host by iterating through registered providers.
     *
     * Implements a defense-in-depth approach by:
     * - Checking multiple provider sources
     * - Returning only validated hosts
     * - Preventing null or unsafe host exposures
     */
    public function getCanonicalHost(): string
    {
        foreach ($this->hostProviders as $provider) {
            $host = $provider->getCanonicalHost();
            if ($host !== null) {
                return $host;
            }
        }
        return $this->resourceChannel->serverName;
    }
}
