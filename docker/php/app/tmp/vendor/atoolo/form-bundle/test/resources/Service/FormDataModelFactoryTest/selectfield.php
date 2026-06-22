<?php

declare(strict_types=1);

return [
    "schema" => [
        "type" => "object",
        "properties" => [
            "field" => [
                "type" => "string",
                "title" => "Selection list",
                "oneOf" => [
                    [
                        "const" => "Dog",
                        "title" => "dog",
                    ],
                    [
                        "const" => "cat",
                        "title" => "Cat",
                    ],
                    [
                        "const" => "mouse",
                        "title" => "Mouse",
                    ],
                ],
            ],
        ],
    ],
    "uischema" => [
        "type" => "VerticalLayout",
        "elements" => [
            [
                "type" => "Control",
                "scope" => "#/properties/field",
                "label" => "Selection list",
            ],
        ],
    ],
    "data" => [
        'field' => 'cat',
    ],
    "expected" => [
        [
            'type' => 'vertical_layout',
            'layout' => true,
            'items' => [
                [
                    'type' => 'select',
                    'name' => 'field',
                    'label' => 'Selection list',
                    'options' => [
                        [
                            'label' => 'dog',
                            'value' => 'Dog',
                            'selected' => false,
                        ],
                        [
                            'label' => 'Cat',
                            'value' => 'cat',
                            'selected' => true,
                        ],
                        [
                            'label' => 'Mouse',
                            'value' => 'mouse',
                            'selected' => false,
                        ],
                    ],
                    'value' => 'Cat',
                ],
            ],
        ],
    ],
];
