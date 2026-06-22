<?php

declare(strict_types=1);

namespace Atoolo\Rewrite\Dto;

use LogicException;

class Url
{
    /**
     * @var array<string>
     */
    private static array $NON_NETWORK_SCHEMES = ['data', 'tel', 'sms', 'urn', 'data'];

    /**
     * @param array<string,mixed>|null $params
     */
    public function __construct(
        public readonly ?string $scheme,
        public readonly ?string $host,
        public readonly ?int $port,
        public readonly ?string $user,
        public readonly ?string $password,
        public readonly ?string $path,
        public readonly ?array $params,
        public readonly ?string $fragment,
        public readonly int $paramEncType,
    ) {}

    public static function builder(): UrlBuilder
    {
        return new UrlBuilder();
    }

    public function toBuilder(): UrlBuilder
    {
        return self::builder()->url($this);
    }

    /**
     * Returns the basename of the path that is set to path and does not end with `/`.
     */
    public function getBasename(): ?string
    {
        if ($this->path === null) {
            return null;
        }
        return basename($this->path);
    }

    /**
     * Returns the suffix of the basename if `getBasename()` does
     * not return null and contains a dot.
     */
    public function getSuffix(): ?string
    {
        $basename = $this->getBasename();
        if ($basename === null) {
            return null;
        }
        $lastDot = strrpos($basename, '.');
        if ($lastDot > 0 && strlen($basename) > $lastDot) {
            return substr($basename, $lastDot + 1);
        }

        return null;
    }

    /**
     * Specifies whether it is a complete URL (starting with [scheme]://).
     * Strictly speaking, a path must also be specified. However, this is ignored as
     * for HTTP urls <code>http://www.domain.de</code> and <code>http://www.domain.de/</code>
     * are equivalent due to the redirects returned by the web server.
     */
    public function isFullyQualified(): bool
    {
        return $this->scheme !== null && $this->host !== null;
    }

    /**
     * Indicates whether the URL is relative. This is the case if no schema and no host exist and
     * the path does not begin with a <code>/</code>.
     */
    public function isRelative(): bool
    {
        if ($this->isFullyQualified()) {
            return false;
        }
        if ($this->path === null) {
            return false;
        }
        return !str_starts_with($this->path, '/');
    }

    /**
     * Converts a relative URL into a complete URL.
     * This object itself is the basis with which the
     * transferred URL is converted into a complete URL.
     */
    public function toFullyQualified(Url $url): Url
    {
        if (!$this->isFullyQualified()) {
            throw new LogicException('The base URL must be fully qualified');
        }

        if ($url->isFullyQualified()) {
            return $url;
        }

        $builder = $this->toBuilder()
            ->path($url->path)
            ->params($url->params)
            ->fragment($url->fragment);


        $path = $url->path;
        if ($path === null) {
            return $builder->build();
        }

        if (
            str_starts_with($path, '/') &&
            !str_contains($path, '/../') &&
            !str_contains($path, '/./')
        ) {
            return $builder->build();
        }

        $basePath = [];
        if (!str_starts_with($path, '/')) {
            $basePath = explode('/', $this->path ?? '');
            $basePath = array_diff($basePath, ['', '.']);
            if (count($basePath) > 0 && !str_ends_with($this->path ?? '', '/')) {
                array_pop($basePath);
            }
        }

        $relativePath = explode('/', $path);
        $relativePath = array_diff($relativePath, ['.']);

        foreach ($relativePath as $name) {
            if ($name === '..') {
                if (count($basePath) > 0) {
                    array_pop($basePath);
                }
            } else {
                $basePath[] = $name;
            }
        }

        $builder->path('/' . implode('/', $basePath));
        return $builder->build();
    }

    /**
     * Returns the base url if the URL is a fully qualified url or null otherwise.
     */
    public function getBaseUrl(): ?string
    {
        if ($this->scheme === null) {
            return null;
        }

        if (in_array($this->scheme, self::$NON_NETWORK_SCHEMES, true)) {
            return $this->scheme . ':';
        }

        $s = [];
        $s[] = $this->scheme;
        $s[] = '://';
        if ($this->user !== null) {
            $s[] = $this->user;
            if ($this->password !== null) {
                $s[] = ':';
                $s[] = $this->password;
            }
            $s[] = '@';
        }
        if ($this->host !== null) {
            $s[] = $this->host;
        }
        if ($this->port !== null) {
            $s[] = ':';
            $s[] = $this->port;
        }
        return implode('', $s);
    }

    public function __toString(): string
    {
        $s = [];

        $baseUrl = $this->getBaseUrl();
        if ($baseUrl !== null) {
            $s[] = $baseUrl;
        }

        if ($this->path !== null) {
            $s[] = $this->path;
        }
        if ($this->params !== null && count($this->params) > 0) {
            $s[] = '?';
            $s[] = http_build_query($this->params, '', '&', $this->paramEncType);
        }
        if ($this->fragment !== null) {
            $s[] = '#';
            $s[] = $this->fragment;
        }

        return implode('', $s);
    }
}
