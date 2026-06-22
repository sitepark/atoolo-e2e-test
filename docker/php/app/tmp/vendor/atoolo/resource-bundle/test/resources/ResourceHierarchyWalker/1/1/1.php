<?php

declare(strict_types=1);

use Atoolo\Resource\Test\TestResourceFactory;

return TestResourceFactory::create([
    'url' => '/1/1/1.php',
    'id' => '1-1-1',
    'name' => '1-1-1',
    'locale' => 'en_US',
    'base' => [
        'trees' => [
            'category' => [
                'parents' => [
                    '1-1' => [
                        'url' => '/1/1.php',
                    ],
                ],
                'children' => [
                    '1-1-1-1' => [
                        'id' => '1-1-1-1',
                        'url' => '/1/1/1/1.php',
                    ],
                ],
            ],
        ],
    ],
]);
