<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Check\Service;

/**
 * @codeCoverageIgnore
 */
class Platform
{
    public function time(): int
    {
        return time();
    }

    public function getUser(): string
    {
        return (posix_getpwuid(posix_geteuid())
            ?: ['name' => (string) posix_geteuid()])['name'];
    }

    public function getGroup(): string
    {
        return (posix_getgrgid(posix_getegid())
            ?: ['name' => (string) posix_getegid()])['name'];
    }

    /**
     * @return array<string,mixed>
     */
    public function getFpmPoolStatus(): array
    {
        return fpm_get_status() ?: [];
    }

    public function getVersion(): string
    {
        return PHP_VERSION;
    }

    public function getPhpIniLoadedFile(): false|string
    {
        return php_ini_loaded_file();
    }

    public function getIni(string $name): string|false
    {
        return ini_get($name);
    }

    /**
     * @return array<string,mixed>
     */
    public function getOpcacheGetStatus(): array
    {
        return opcache_get_status() ?: [];
    }
}
