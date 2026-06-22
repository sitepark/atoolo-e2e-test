<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Dto;

/**
 * @codeCoverageIgnore
 */
class EmailAlreadyExistsError
{
    public function __construct(
        public readonly string $email,
    ) {}
}
