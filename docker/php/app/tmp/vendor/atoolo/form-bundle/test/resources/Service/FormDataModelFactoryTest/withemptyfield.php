<?php

declare(strict_types=1);

return [
    "schema" => [
        "type" => "object",
        "properties" => [
            "field-1" => [
                "type" => "string",
                "title" => "Field 1",
            ],
            "field-2" => [
                "type" => "string",
                "title" => "Field 2",
            ],
        ],
    ],
    "uischema" => [
        "type" => "VerticalLayout",
        "elements" => [
            [
                "type" => "Control",
                "scope" => "#/properties/field-1",
                "label" => "Field 1",
            ],
            [
                "type" => "Control",
                "scope" => "#/properties/field-2",
                "label" => "Field 2",
            ],
        ],
    ],
    "data" => [
        'field-1' => 'text',
    ],
    "expected" => [
        [
            'type' => 'vertical_layout',
            'layout' => true,
            'items' => [
                [
                    'type' => 'text',
                    'name' => 'field-1',
                    'label' => 'Field 1',
                    'value' => 'text',
                ],
            ],
        ],
    ],
];
