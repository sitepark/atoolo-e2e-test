<?php

declare(strict_types=1);

return [
    "schema" => [
        "type" => "object",
        "properties" => [
            "field" => [
                "type" => "string",
                "title" => "Rich text input field",
                "format" => "html",
                "allowedElements" => [
                    "p",
                    "strong",
                    "em",
                    "li",
                    "ul",
                    "ol",
                ],
            ],
        ],
    ],
    "uischema" => [
        "type" => "VerticalLayout",
        "elements" => [
            [
                "type" => "Control",
                "scope" => "#/properties/field",
                "label" => "Rich text input field",
                "options" => [
                    "multi" => true,
                    "html" => true,
                ],
            ],
        ],
    ],
    "data" => [
        'field' => '<p>text <strong>abc</strong></p>',
    ],
    "expected" => [
        [
            'type' => 'vertical_layout',
            'layout' => true,
            'items' => [
                [
                    'type' => 'html',
                    'name' => 'field',
                    'label' => 'Rich text input field',
                    'value' => '<p>text <strong>abc</strong></p>',
                ],
            ],
        ],
    ],
];
