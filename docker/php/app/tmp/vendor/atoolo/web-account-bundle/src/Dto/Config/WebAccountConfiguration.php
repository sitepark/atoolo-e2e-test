<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Dto\Config;

/**
 * @codeCoverageIgnore
 */
class WebAccountConfiguration
{
    public function __construct(
        public readonly string $name,
        public readonly string $apiKey,
        public readonly RegistrationConfiguration $registration,
        public readonly EmailConfiguration $email,
    ) {}
}
