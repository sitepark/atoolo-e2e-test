<?php

declare(strict_types=1);

return [
    "schema" => [
        "type" => "object",
        "properties" => [
            "field" => [
                "type" => "string",
                "title" => "Multiline text field",
            ],
        ],
    ],
    "uischema" => [
        "type" => "VerticalLayout",
        "elements" => [
            [
                "type" => "Control",
                "scope" => "#/properties/field",
                "label" => "Multiline text field",
                "options" => [
                    "autocomplete" => "off",
                    "multi" => true,
                ],
            ],
        ],
    ],
    "data" => [
        'field' => "line1\nline2\n",
    ],
    "expected" => [
        [
            'type' => 'vertical_layout',
            'layout' => true,
            'items' => [
                [
                    'type' => 'text',
                    'name' => 'field',
                    'label' => 'Multiline text field',
                    'value' => "line1\nline2\n",
                ],
            ],
        ],
    ],
];
