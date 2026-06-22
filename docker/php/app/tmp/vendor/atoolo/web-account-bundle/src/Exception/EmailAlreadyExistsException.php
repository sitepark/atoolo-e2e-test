<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Exception;

use Exception;

/**
 * @codeCoverageIgnore
 */
class EmailAlreadyExistsException extends Exception
{
    public function __construct(
        public readonly string $email,
    ) {
        parent::__construct("The email address '{$email}' is already associated with an existing account.");
    }
}
