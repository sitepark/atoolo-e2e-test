<?php

declare(strict_types=1);

return [
    "schema" => [
        "type" => "object",
        "properties" => [
            "field" => [
                "type" => "array",
                "title" => "Checkbox group",
                "items" => [
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
                "uniqueItems" => true,
            ],
        ],
    ],
    "uischema" => [
        "type" => "VerticalLayout",
        "elements" => [
            [
                "type" => "Control",
                "scope" => "#/properties/field",
                "label" => "Checkbox group",
            ],
        ],
    ],
    "data" => [
        'field' => ['cat', 'mouse'],
    ],
    "expected" => [
        [
            'type' => 'vertical_layout',
            'layout' => true,
            'items' => [
                [
                    'type' => 'checkbox-group',
                    'name' => 'field',
                    'label' => 'Checkbox group',
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
                            'selected' => true,
                        ],
                    ],
                    'value' => ['Cat', 'Mouse'],
                ],
            ],
        ],
    ],
];
