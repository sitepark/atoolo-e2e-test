<?php

declare(strict_types=1);

use Atoolo\Resource\Test\TestResourceFactory;

return TestResourceFactory::create([
    'url' => '/2/1.php',
    'id' => '2-1',
    'name' => '2-1',
    'locale' => 'en_US',
    'base' => [
        'trees' => [
            'category' => [
                'parents' => [
                    '2' => [
                        'url' => '/2.php',
                    ],
                ],
            ],
        ],
    ],
]);
