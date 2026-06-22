<?php

declare(strict_types=1);

use Atoolo\Resource\Test\TestResourceFactory;

return TestResourceFactory::create([
    'url' => '/firstParentWithNonStringUrl.php',
    'id' => 'firstParentWithNonStringUrl',
    'name' => 'firstParentWithNonStringUrl',
    'locale' => 'en_US',
    'base' => [
        'trees' => [
            'category' => [
                'parents' => [
                    'a' => [
                        'url' => false,
                    ],
                ],
            ],
        ],
    ],
]);
