<?php

declare(strict_types=1);

namespace Atoolo\EventsCalendar\Service;

use RuntimeException;

/**
 * @codeCoverageIgnore
 */
class Platform
{
    /**
     * @throws RuntimeException
     */
    public function unlink(string $file): void
    {
        if (unlink($file) === false) {
            throw new RuntimeException('Could not delete file: ' . $file);
        }
    }

    public function tempnam(string $dir, string $prefix): string
    {
        $file = tempnam($dir, $prefix);
        if ($file === false) {
            throw new RuntimeException('Could not create temporary file in directory ' . $dir);
        }

        return $file;
    }

    public function rename(string $from, string $to): void
    {
        if (@rename($from, $to) !== true) {
            throw new RuntimeException(
                'Unable to rename temp file from ' . $from . ' to ' . $to,
            );
        }
    }

    public function is_dir(string $dir): bool
    {
        return is_dir($dir);
    }

    public function is_writeable(string $dir): bool
    {
        return is_writable($dir);
    }

    public function mkdir(string $dir): void
    {
        if (!@mkdir($concurrentDirectory = $dir, 0777, true) && !is_dir($concurrentDirectory)) {
            throw new RuntimeException(
                sprintf('Directory "%s" was not created', $concurrentDirectory),
            );
        }
    }
}
