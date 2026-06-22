<?php

declare(strict_types=1);

return [
    "schema" => [
        "type" => "object",
        "properties" => [
            "field" => [
                "type" => "string",
                "title" => "email",
                "format" => "email",
            ],
        ],
    ],
    "uischema" => [
        "type" => "VerticalLayout",
        "elements" => [
            [
                "type" => "Control",
                "scope" => "#/properties/field",
                "label" => "Email",
                "options" => [
                    "autocomplete" => "email",
                    "asReplyTo" => true,
                ],
            ],
        ],
    ],
    "data" => [
        'field' => 'test@example.com',
    ],
    "expected" => [
        [
            'type' => 'vertical_layout',
            'layout' => true,
            'items' => [
                [
                    'type' => 'text',
                    'name' => 'field',
                    'label' => 'Email',
                    'value' => 'test@example.com',
                ],
            ],
        ],
    ],
];
