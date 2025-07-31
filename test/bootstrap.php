<?php

declare(strict_types=1);


include __DIR__ . '/../vendor/autoload.php';

$_SERVER['ENDPOINT_BASE'] = $_SERVER['ENDPOINT_BASE'] ?? 'http://www-atoolo-e2e-test:9090';
$_SERVER['MAILPIT_ENDPOINT_BASE'] = $_SERVER['MAILPIT_ENDPOINT_BASE'] ?? 'http://atoolo-e2e-test:8025';
$_SERVER['DOCKER_COMPOSE_PROJECT_NAME'] = $_SERVER['DOCKER_COMPOSE_PROJECT_NAME'] ?? 'atoolo-e2e-test';

echo "\nTest environment variables:\n";
echo "ENDPOINT_BASE: " . $_SERVER['ENDPOINT_BASE'] . "\n";
echo "MAILPIT_ENDPOINT_BASE: " . $_SERVER['MAILPIT_ENDPOINT_BASE'] . "\n";
echo "DOCKER_COMPOSE_PROJECT_NAME: " . $_SERVER['DOCKER_COMPOSE_PROJECT_NAME'] . "\n";
echo "\n";