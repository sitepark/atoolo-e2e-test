<?php

declare(strict_types=1);

return [
    "schema" => [
        "type" => "object",
        "properties" => [
            "field" => [
                "type" => "boolean",
                "title" => "Checkbox",
            ],
        ],
    ],
    "uischema" => [
        "type" => "VerticalLayout",
        "elements" => [
            [
                "type" => "Control",
                "scope" => "#/properties/field",
                "label" => "Checkbox",
                "options" => [
                    "label" => "Checkbox",
                ],
            ],
        ],
    ],
    "data" => [
        'field' => true,
    ],
    "expected" => [
        [
            'type' => 'vertical_layout',
            'layout' => true,
            'items' => [
                [
                    'type' => 'checkbox',
                    'name' => 'field',
                    'label' => 'Checkbox',
                    'value' => true,
                ],
            ],
        ],
    ],
];
