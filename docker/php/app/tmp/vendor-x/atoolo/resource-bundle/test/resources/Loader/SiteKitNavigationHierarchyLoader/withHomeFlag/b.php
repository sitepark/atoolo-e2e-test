<?php

declare(strict_types=1);

use Atoolo\Resource\Test\TestResourceFactory;

return TestResourceFactory::create([
    'url' => '/b.php',
    'id' => 'b',
    'name' => 'b',
    'locale' => 'en_US',
    'base' => [
        'trees' => [
            'navigation' => [
                'parents' => [
                    'a' => [
                        'url' => '/a.php',
                    ],
                ],
            ],
        ],
    ],
]);
