<?php

declare(strict_types=1);

namespace Atoolo\Resource;

class ResourceLocation
{
    private function __construct(
        public readonly string $location,
        public readonly ResourceLanguage $lang,
    ) {}

    public static function of(
        string $location,
        ?ResourceLanguage $lang = null,
    ): self {
        return new self(
            $location,
            $lang ?? ResourceLanguage::default(),
        );
    }

    public static function ofPath(
        string $path,
        ?ResourceLanguage $lang = null,
    ): self {
        if (str_ends_with($path, '.php')) {
            return self::of($path, $lang);
        }
        if (str_ends_with($path, '/')) {
            return self::of($path . 'index.php', $lang);
        }
        return self::of($path . '.php', $lang);
    }

    public function __toString(): string
    {
        if ($this->lang->code === '') {
            return $this->location;
        }
        return $this->location . ':' . $this->lang->code;
    }
}
