<?php

declare(strict_types=1);

use Atoolo\Resource\Test\TestResourceFactory;

return TestResourceFactory::create([
    'url' => '/primaryParentWithNonStringUrl.php',
    'id' => 'primaryParentWithNonStringUrl',
    'name' => 'primaryParentWithNonStringUrl',
    'locale' => 'en_US',
    'base' => [
        'trees' => [
            'category' => [
                'parents' => [
                    'a' => [
                        'isPrimary' => true,
                        'url' => false,
                    ],
                ],
            ],
        ],
    ],
]);
