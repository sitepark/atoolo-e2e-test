<?php

declare(strict_types=1);

return [
    "schema" => [
    ],
    "uischema" => [
        "type" => "VerticalLayout",
        "elements" => [
            [
                "type" => "Annotation",
                "htmlLabel" => [
                    "text" => "Note with <strong>html</strong> and <a href=\"/link.php\">link</a>.",
                ],
            ],
        ],
    ],
    "data" => [
    ],
    "expected" => [
        [
            'type' => 'vertical_layout',
            'layout' => true,
            'items' => [
            ],
        ],
    ],
];
