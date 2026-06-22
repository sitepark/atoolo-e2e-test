<?php

declare(strict_types=1);

namespace Atoolo\Rewrite\Dto;

final class UrlRewriteOptions
{
    public function __construct(
        public readonly bool $toFullyQualifiedUrl,
        public readonly ?string $lang,
    ) {}

    public static function none(): UrlRewriteOptions
    {
        return (new UrlRewriteOptionsBuilder())->build();
    }

    public static function builder(): UrlRewriteOptionsBuilder
    {
        return new UrlRewriteOptionsBuilder();
    }
}
