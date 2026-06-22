<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Dto;

/**
 * @codeCoverageIgnore
 */
class StartRegistrationRequest
{
    public function __construct(
        public string $configName,
        public string $lang,
        public string $emailAddress,
    ) {}
}
