<?php

declare(strict_types=1);

namespace Atoolo\Rewrite\Dto;

final class UrlRewriteOptionsBuilder
{
    private bool $toFullyQualifiedUrl = false;

    private ?string $lang = null;

    public function __construct() {}

    public function toFullyQualifiedUrl(bool $toFullyQualifiedUrl): self
    {
        $this->toFullyQualifiedUrl = $toFullyQualifiedUrl;
        return $this;
    }

    public function lang(?string $lang): self
    {
        $this->lang = $lang;
        return $this;
    }

    public function build(): UrlRewriteOptions
    {
        return new UrlRewriteOptions(
            $this->toFullyQualifiedUrl,
            $this->lang,
        );
    }
}
