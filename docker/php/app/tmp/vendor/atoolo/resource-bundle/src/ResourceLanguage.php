<?php

declare(strict_types=1);

namespace Atoolo\Resource;

use Locale;

class ResourceLanguage
{
    private static ?ResourceLanguage $DEFAULT = null;

    private function __construct(
        public readonly string $code,
    ) {}

    public static function default(): self
    {
        return self::$DEFAULT ??= new self('');
    }

    public static function of(?string $str): self
    {
        if ($str === null || empty($str)) {
            return self::default();
        }
        $code = Locale::getPrimaryLanguage($str);
        return new self($code ?? '');
    }
}
