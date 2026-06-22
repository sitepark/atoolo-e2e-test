<?php

declare(strict_types=1);

namespace Atoolo\Resource\Exception;

use Atoolo\Resource\ResourceLocation;

/**
 * This exception is used when a resource is invalid. This can have the
 * following reasons:
 *
 * - If the resource is syntactically incorrect.
 * - If the resource does not contain necessary data.
 */
class InvalidResourceException extends \RuntimeException
{
    public function __construct(
        private readonly ResourceLocation $location,
        string $message = "",
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            $location->__toString() . ': ' . $message,
            $code,
            $previous,
        );
    }

    public function getLocation(): ResourceLocation
    {
        return $this->location;
    }
}
