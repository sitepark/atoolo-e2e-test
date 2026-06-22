<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Dto;

/**
 * @codeCoverageIgnore
 */
class FinishRegistrationResult
{
    public function __construct(
        public string $id,
        public string $email,
    ) {}
}
