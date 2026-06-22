<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Dto;

use DateTime;

/**
 * @codeCoverageIgnore
 */
class StartPasswordRecoveryResult
{
    public function __construct(
        public string $challengeId,
        public DateTime $createAt,
        public DateTime $expiresAt,
    ) {}
}
