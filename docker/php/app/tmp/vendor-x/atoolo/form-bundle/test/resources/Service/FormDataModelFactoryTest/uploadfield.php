<?php

declare(strict_types=1);

return [
    "schema" => [
        "type" => "object",
        "properties" => [
            "field" => [
                "type" => "string",
                "title" => "File upload",
                "acceptedFileNames" => [
                    "*.png",
                    "*.jpg",
                ],
                "maxFileSize" => 2000000,
                "acceptedContentTypes" => ["image/*"],
                "format" => "data-url",
            ],
        ],
    ],
    "uischema" => [
        "type" => "VerticalLayout",
        "elements" => [
            [
                "type" => "Control",
                "scope" => "#/properties/field",
                "label" => "File upload",
                "options" => [
                    "spaceAfter" => true,
                ],
            ],
        ],
    ],
    "data" => [
        'field' => 'data:text/plain;name=text.txt;base64,dGV4dAo=',
    ],
    "expected" => [
        [
            'type' => 'vertical_layout',
            'layout' => true,
            'items' => [
                [
                    'type' => 'file',
                    'name' => 'field',
                    'label' => 'File upload',
                    'value' => [
                        'filename' => 'text.txt',
                        'data' => 'text',
                        'size' => 4,
                        'contentType' => 'text/plain',
                    ],
                ],
            ],
        ],
    ],
];
