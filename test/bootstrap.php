<?php

declare(strict_types=1);


include __DIR__ . '/../vendor/autoload.php';

$_SERVER['ENDPOINT_BASE'] = $_SERVER['ENDPOINT_BASE'] ?? 'http://atoolo-e2e-test:9090';
$_SERVER['MAILPIT_ENDPOINT_BASE'] = $_SERVER['MAILPIT_ENDPOINT_BASE'] ?? 'http://atoolo-e2e-test:9090';

echo "ENDPOINT_BASE: " . $_SERVER['ENDPOINT_BASE'] . "\n";
echo "MAILPIT_ENDPOINT_BASE: " . $_SERVER['MAILPIT_ENDPOINT_BASE'] . "\n";
