<?php

declare(strict_types=1);

use Atoolo\Resource\Test\TestResourceFactory;

return TestResourceFactory::create([
    'url' => '/childrenWithInvalidData.php',
    'id' => 'childrenWithInvalidData',
    'name' => 'childrenWithInvalidData',
    'locale' => 'en_US',
    'base' => [
        'trees' => [
            'category' => [
                'children' => [
                    'a' => 'invalid',
                ],
            ],
        ],
    ],
]);
