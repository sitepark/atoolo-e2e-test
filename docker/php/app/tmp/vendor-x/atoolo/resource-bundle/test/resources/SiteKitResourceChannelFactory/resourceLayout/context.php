<?php

return [
    'server' => [
        'host' => 'test-host',
    ],
    'tenant' => [
        'id' => '2',
        'name' => 'Test-Tanent',
        'anchor' => 'test-tanent',
        'attributes' => [
            'abc' => 'cde',
        ],
    ],
    'publisher' => [
        'id' => '1',
        'name' => 'Test',
        'anchor' => 'test',
        'serverName' => 'www.test.org',
        'preview' => true,
        'nature' => 'internet',
        'locale' => 'de_DE',
        'encoding' => 'UTF-8',
        'attributes' => [
            'hello' => 'world',
            'abc' => 'xyz',
        ],
    ],
];
