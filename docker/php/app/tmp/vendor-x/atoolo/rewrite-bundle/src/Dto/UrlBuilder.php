<?php

declare(strict_types=1);

namespace Atoolo\Rewrite\Dto;

class UrlBuilder
{
    private ?string $scheme = null;

    private ?string $host = null;

    private ?int $port = null;

    private ?string $user = null;

    private ?string $password = null;

    private ?string $path = null;

    /**
     * @var array<string,mixed>
     */
    private ?array $params = null;

    private ?string $fragment = null;

    private int $paramEncType = PHP_QUERY_RFC1738;

    public function __construct() {}

    public function url(Url $url): static
    {
        return $this->scheme($url->scheme)
            ->user($url->user)
            ->password($url->password)
            ->host($url->host)
            ->port($url->port)
            ->path($url->path)
            ->params($url->params)
            ->fragment($url->fragment)
            ->paramEncType($url->paramEncType);
    }

    public function parse(string $url): static
    {
        $urlParts = parse_url($url);
        $this->scheme($urlParts['scheme'] ?? null);
        $this->host($urlParts['host'] ?? null);
        $this->port($urlParts['port'] ?? null);
        $this->user($urlParts['user'] ?? null);
        $this->password($urlParts['pass'] ?? null);
        $this->path($urlParts['path'] ?? null);
        $this->query($urlParts['query'] ?? null);
        $this->fragment($urlParts['fragment'] ?? null);

        return $this;
    }

    public function scheme(?string $scheme): static
    {
        $this->scheme = $scheme;
        return $this;
    }

    public function host(?string $host): static
    {
        $this->host = $host;
        return $this;
    }

    public function port(?int $port): static
    {
        $this->port = $port;
        return $this;
    }

    public function user(?string $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function password(?string $password): static
    {
        $this->password = $password;
        return $this;
    }

    public function query(?string $query): static
    {
        if ($query === null) {
            $this->params = null;
            return $this;
        }

        $params = [];
        parse_str($query, $params);

        /** @var array<string,mixed> $params */
        $this->params = $params;

        return $this;
    }

    public function path(?string $path): static
    {
        $this->path = $path;
        return $this;
    }

    public function param(string $key, string $value): static
    {
        $this->params[$key] = $value;
        return $this;
    }

    /**
     * @param array<string,mixed> $params
     * @return $this
     */
    public function params(?array $params): static
    {
        $this->params = $params;
        return $this;
    }


    public function fragment(?string $fragment): static
    {
        $this->fragment = $fragment;
        return $this;
    }

    public function paramEncType(int $paramEncType): static
    {
        $this->paramEncType = $paramEncType;
        return $this;
    }

    public function build(): Url
    {
        return new Url(
            $this->scheme,
            $this->host,
            $this->port,
            $this->user,
            $this->password,
            $this->path,
            $this->params,
            $this->fragment,
            $this->paramEncType,
        );
    }
}
