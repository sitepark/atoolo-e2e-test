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
        'field' => [],
    ],
    "expected" => [
        [
            'type' => 'vertical_layout',
            'layout' => true,
            'items' => [
            ],
        ],
    ],
];
