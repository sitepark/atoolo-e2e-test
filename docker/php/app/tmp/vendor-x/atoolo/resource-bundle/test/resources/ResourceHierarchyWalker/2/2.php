<?php

declare(strict_types=1);

use Atoolo\Resource\Test\TestResourceFactory;

return TestResourceFactory::create([
    'url' => '/2/2.php',
    'id' => '2-2',
    'name' => '2-2',
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
