<?php

declare(strict_types=1);

use Atoolo\Resource\Test\TestResourceFactory;

return TestResourceFactory::create([
    'url' => '/primaryParentWithoutUrl.php',
    'id' => 'primaryParentWithoutUrl',
    'name' => 'primaryParentWithoutUrl',
    'locale' => 'en_US',
    'base' => [
        'trees' => [
            'category' => [
                'parents' => [
                    'a' => [
                        'isPrimary' => true,
                    ],
                ],
            ],
        ],
    ],
]);
