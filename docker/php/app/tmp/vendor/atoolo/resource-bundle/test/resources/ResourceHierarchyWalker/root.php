<?php

declare(strict_types=1);

use Atoolo\Resource\Test\TestResourceFactory;

return TestResourceFactory::create([
    'url' => '/root.php',
    'id' => 'root',
    'name' => 'root',
    'locale' => 'en_US',
    'base' => [
        'trees' => [
            'category' => [
                'children' => [
                    '1 1' => [
                        'id' => '1',
                        'url' => '/1.php',
                    ],
                    '2' => [
                        'id' => '2',
                        'url' => '/2.php',
                    ],
                ],
            ],
        ],
    ],
]);
