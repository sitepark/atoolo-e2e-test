<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Dto;

/**
 * @codeCoverageIgnore
 */
class FinishPasswordRecoveryRequest
{
    public function __construct(
        public string $configName,
        public string $lang,
        public string $challengeId,
        public int $code,
        public string $newPassword,
    ) {}
}
