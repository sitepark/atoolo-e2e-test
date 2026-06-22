<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Service;

use Exception;

class ServiceException extends Exception
{
    /**
     * @param string $message
     * @param Exception|null $previous
     */
    public function __construct(
        string $message,
        ?Exception $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
