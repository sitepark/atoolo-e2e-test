<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Executor;

use RuntimeException;
use Symfony\Component\Dotenv\Dotenv;

/**
 * @codeCoverageIgnore
 */
class Platform
{
    private Dotenv $dotenv;

    public function __construct()
    {
        $this->dotenv = new Dotenv();
    }

    public function umask(int $umask): int
    {
        return umask($umask);
    }

    public function setIni(
        string $name,
        bool|float|int|string $value,
    ): false|string {
        return ini_set($name, $value);
    }

    public function readFile(string $file): string
    {
        if (!file_exists($file)) {
            throw new RuntimeException("File not exists: {$file}");
        }

        if (!is_readable($file)) {
            throw new RuntimeException("File is not readable: {$file}");
        }

        $content = file_get_contents($file);
        if ($content === false) {
            throw new RuntimeException("Failed to read file: {$file}");
        }
        return $content;
    }

    public function putEnv(
        string $name,
        string $value,
    ): bool {
        $this->dotenv->populate([$name => $value]);
        return true;
    }
}
