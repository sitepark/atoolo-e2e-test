<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Dto\Config;

/**
 * @codeCoverageIgnore
 */
class RegistrationConfiguration
{
    /**
     * @param array<string> $roleIds
     */
    public function __construct(
        public readonly array $roleIds,
    ) {}
}
