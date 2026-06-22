<?php

declare(strict_types=1);

return [
    "schema" => [
        "type" => "object",
        "properties" => [
            "field" => [
                "type" => "string",
                "title" => "Single-line text field",
            ],
        ],
    ],
    "uischema" => [
        "type" => "VerticalLayout",
        "elements" => [
            [
                "type" => "Control",
                "scope" => "#/properties/field",
                "label" => "Single-line text field",
                "options" => [
                    "autocomplete" => "name",
                    "spaceAfter" => true,
                ],
            ],
        ],
    ],
    "data" => [
        'field' => 'text',
    ],
    "expected" => [
        [
            'type' => 'vertical_layout',
            'layout' => true,
            'items' => [
                [
                    'type' => 'text',
                    'name' => 'field',
                    'label' => 'Single-line text field',
                    'value' => 'text',
                ],
            ],
        ],
    ],
];
