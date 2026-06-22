<?php

declare(strict_types=1);

use Atoolo\Resource\Test\TestResourceFactory;

return TestResourceFactory::create([
    'url' => '/dir/c.php',
    'id' => 'c',
    'name' => 'c',
    'locale' => 'en_US',
    'base' => [
        'trees' => [
            'navigation' => [
                'parents' => [
                    'b' => [
                        'isPrimary' => true,
                        'url' => '/dir/b.php',
                    ],
                    'a' => [
                        'url' => '/dir/a.php',
                    ],
                ],
            ],
        ],
    ],
]);
