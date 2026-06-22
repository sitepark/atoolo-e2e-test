<?php

declare(strict_types=1);

namespace Atoolo\Microsite\Service;

/**
 * @codeCoverageIgnore
 */
class Platform
{
    public function fileExists(string $path): bool
    {
        return file_exists($path);
    }
}
