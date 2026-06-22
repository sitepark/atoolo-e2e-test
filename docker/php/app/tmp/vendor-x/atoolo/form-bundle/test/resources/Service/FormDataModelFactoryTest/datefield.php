<?php

declare(strict_types=1);

return [
    "schema" => [
        "type" => "object",
        "properties" => [
            "field" => [
                "type" => "string",
                "title" => "Date",
                "format" => "date",
            ],
        ],
    ],
    "uischema" => [
        "type" => "VerticalLayout",
        "elements" => [
            [
                "type" => "Control",
                "scope" => "#/properties/field",
                "label" => "Date",
            ],
        ],
    ],
    "data" => [
        'field' => '2024-09-23',
    ],
    "expected" => [
        [
            'type' => 'vertical_layout',
            'layout' => true,
            'items' => [
                [
                    'type' => 'date',
                    'name' => 'field',
                    'label' => 'Date',
                    'value' => '2024-09-23',
                ],
            ],
        ],
    ],
];
