<?php

declare(strict_types=1);

use Atoolo\Resource\Test\TestResourceFactory;

return TestResourceFactory::create([
    'url' => '/withSelfRecursion.php',
    'id' => 'withRecursion',
    'name' => 'withRecursion',
    'locale' => 'en_US',
    'base' => [
        'trees' => [
            'category' => [
                'parents' => [
                    'withRecursion' => [
                        'url' => '/withSelfRecursion.php',
                    ],
                ],
            ],
        ],
    ],
]);
