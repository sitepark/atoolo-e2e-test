<?php

declare(strict_types=1);

namespace Atoolo\Resource\Exception;

/**
 * Is used by the ResourceHierarchyLoader to indicate that the requested
 * data could not be determined because no root element can be resolved.
 */
class RootMissingException extends \RuntimeException
{
    public function __construct(
        private readonly string $location,
        string $message = "",
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            $location . ': ' . $message,
            $code,
            $previous,
        );
    }

    public function getLocation(): string
    {
        return $this->location;
    }
}
