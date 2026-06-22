<?php

declare(strict_types=1);

return [
    "schema" => [
        "type" => "object",
        "properties" => [
            "field" => [
                "type" => "boolean",
                "title" => "Checkbox with Html Label",
            ],
        ],
    ],
    "uischema" => [
        "type" => "VerticalLayout",
        "elements" => [
            [
                "type" => "Control",
                "scope" => "#/properties/field",
                "options" => [],
                "htmlLabel" => [
                    "text" => "An <strong>html</strong> label with <a href=\"/link.php\">link</a>.",
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
                    'value' => true,
                    'htmlLabel' => [
                        'text' => 'An <strong>html</strong> label with <a href="/link.php">link</a>.',
                    ],
                ],
            ],
        ],
    ],
];
