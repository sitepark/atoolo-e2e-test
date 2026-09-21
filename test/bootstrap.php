<?php

declare(strict_types=1);


include __DIR__ . '/../vendor/autoload.php';

$_SERVER['ENDPOINT_BASE'] = $_SERVER['ENDPOINT_BASE'] ?? 'http://www-atoolo-e2e-test:9090';
$_SERVER['MAILPIT_ENDPOINT_BASE'] = $_SERVER['MAILPIT_ENDPOINT_BASE'] ?? 'http://atoolo-e2e-test:8025';
$_SERVER['DOCKER_COMPOSE_PROJECT_NAME'] = $_SERVER['DOCKER_COMPOSE_PROJECT_NAME'] ?? 'atoolo-e2e-test';

/**
 * $_SERVER entries are mixed, so read them through one place that
 * guarantees a string.
 */
function e2eEnv(string $name, string $default = ''): string
{
    $value = $_SERVER[$name] ?? $default;
    return is_string($value) ? $value : $default;
}

echo "\nTest environment variables:\n";
echo "ENDPOINT_BASE: " . e2eEnv('ENDPOINT_BASE') . "\n";
echo "MAILPIT_ENDPOINT_BASE: " . e2eEnv('MAILPIT_ENDPOINT_BASE') . "\n";
echo "DOCKER_COMPOSE_PROJECT_NAME: " . e2eEnv('DOCKER_COMPOSE_PROJECT_NAME') . "\n";
echo "\n";
