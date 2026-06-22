<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Dto;

/**
 * @codeCoverageIgnore
 */
class StartPasswordRecoveryRequest
{
    public function __construct(
        public string $configName,
        public string $lang,
        public string $username,
    ) {}
}
