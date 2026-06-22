<?php

declare(strict_types=1);

return [
    "schema" => [
        "type" => "object",
        "properties" => [
            "field" => [
                "type" => "string",
                "title" => "Phone number",
                "format" => "phone",
            ],
        ],
    ],
    "uischema" => [
        "type" => "VerticalLayout",
        "elements" => [
            [
                "type" => "Control",
                "scope" => "#/properties/field",
                "label" => "Phone number",
                "options" => [
                    "autocomplete" => "tel",
                ],
            ],
        ],
    ],
    "data" => [
        'field' => "123",
    ],
    "expected" => [
        [
            'type' => 'vertical_layout',
            'layout' => true,
            'items' => [
                [
                    'type' => 'text',
                    'name' => 'field',
                    'label' => 'Phone number',
                    'value' => '123',
                ],
            ],
        ],
    ],
];
