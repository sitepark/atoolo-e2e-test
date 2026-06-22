<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Dto;

/**
 * @codeCoverageIgnore
 */
class AuthenticationResult
{
    public function __construct(
        public AuthenticationStatus $status,
        public ?User $user = null,
    ) {}
}
