<?php

declare(strict_types=1);

namespace Atoolo\Resource;

/**
 * Retrieves a value from this data structure by name.
 * The name can contain dots (`.`) in case of a nested structure and define
 * the corresponding levels.
 *
 *  Example:
 *  <pre>
 *  [
 *    'foo': [
 *      'bar': 'value'
 *    ]
 *  ]
 *  </pre>
 *
 * For the above structure, `value` can be retrieved using the following
 * `$name`: `'foo.bar'`
 *
 * The desired value can be returned using the corresponding typed
 * getter methods.
 *
 * If $name cannot be resolved to an existing value or the type does not
 * correspond to the expected type, the default value is returned instead.
 */
class DataBag
{
    /**
     * @param array<string, mixed> $data
     */
    public function __construct(private readonly array $data) {}

    /**
     * @return array<string, mixed>
     */
    public function get(): array
    {
        return $this->data;
    }

    public function has(string $name): bool
    {
        return $this->findData($this->data, $name) !== null;
    }

    /**
     * @param array<mixed> $default
     * @return array<mixed>
     */
    public function getArray(string $name, array $default = []): array
    {
        $data = $this->findData($this->data, $name);
        if (is_array($data)) {
            return (array) $data;
        }
        return $default;
    }

    /**
     * @param array<string,mixed> $default
     * @return array<string,mixed>
     */
    public function getAssociativeArray(
        string $name,
        array $default = [],
    ): array {
        $data = $this->findData($this->data, $name);
        if (is_array($data)) {
            return (array) $data;
        }
        return $default;
    }

    public function getString(string $name, string $default = ''): string
    {
        $data = $this->findData($this->data, $name);
        if (is_string($data)) {
            return (string) $data;
        }
        return $default;
    }

    public function getInt(string $name, int $default = 0): int
    {
        $data = $this->findData($this->data, $name);
        if (is_int($data)) {
            return (int) $data;
        }
        return $default;
    }

    public function getFloat(string $name, float $default = 0.0): float
    {
        $data = $this->findData($this->data, $name);
        if (is_float($data)) {
            return (float) $data;
        }
        return $default;
    }

    public function getBool(string $name, bool $default = false): bool
    {
        $data = $this->findData($this->data, $name);
        if (is_bool($data)) {
            return (bool) $data;
        }
        return $default;
    }

    /**
     * @param array<string, mixed> $data
     */
    private function findData(array $data, string $name): mixed
    {
        $names = explode('.', $name);
        foreach ($names as $n) {
            if (is_array($data) && isset($data[$n])) {
                $data = $data[$n];
            } else {
                return null;
            }
        }
        return $data;
    }
}
