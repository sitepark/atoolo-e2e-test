<?php

declare(strict_types=1);

return [
    "schema" => [
        "type" => "object",
        "properties" => [
            "field" => [
                "type" => "string",
                "title" => "Radio buttons",
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
                "label" => "Radio buttons",
                "options" => [
                    "format" => "radio",
                ],
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
                    'type' => 'radio-buttons',
                    'name' => 'field',
                    'label' => 'Radio buttons',
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
