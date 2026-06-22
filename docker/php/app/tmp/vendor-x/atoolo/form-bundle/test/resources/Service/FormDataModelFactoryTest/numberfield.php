<?php

declare(strict_types=1);

return [
    "schema" => [
        "type" => "object",
        "properties" => [
            "field" => [
                "type" => "integer",
                "title" => "Number",
            ],
        ],
    ],
    "uischema" => [
        "type" => "VerticalLayout",
        "elements" => [
            [
                "type" => "Control",
                "scope" => "#/properties/field",
                "label" => "Number",
                "options" => [
                    "autocomplete" => "off",
                ],
            ],
        ],
    ],
    "data" => [
        'field' => 123,
    ],
    "expected" => [
        [
            'type' => 'vertical_layout',
            'layout' => true,
            'items' => [
                [
                    'type' => 'text',
                    'name' => 'field',
                    'label' => 'Number',
                    'value' => 123,
                ],
            ],
        ],
    ],
];
