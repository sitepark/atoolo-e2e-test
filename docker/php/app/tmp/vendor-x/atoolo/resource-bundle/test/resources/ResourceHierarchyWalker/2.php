<?php

declare(strict_types=1);

use Atoolo\Resource\Test\TestResourceFactory;

return TestResourceFactory::create([
    'url' => '/2.php',
    'id' => '2',
    'name' => '2',
    'locale' => 'en_US',
    'base' => [
        'trees' => [
            'category' => [
                'parents' => [
                    'root' => [
                        'url' => '/root.php',
                    ],
                ],
                'children' => [
                    '2-1' => [
                        'id' => '2-1',
                        'url' => '/2/1.php',
                    ],
                    '2-2' => [
                        'id' => '2-2',
                        'url' => '/2/2.php',
                    ],
                ],
            ],
        ],
    ],
]);
