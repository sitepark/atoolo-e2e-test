<?php

declare(strict_types=1);

return [
    "schema" => [
        "type" => "object",
        "properties" => [
            "contact" => [
                "type" => "object",
                "properties" => [
                    "salutation" => [
                        "type" => "string",
                        "enum" => [
                            "salutationFemale",
                            "salutationMale",
                            "salutationDiverse",
                            "salutationNotSpecified",
                        ],
                    ],
                    "firstname" => [
                        "type" => "string",
                    ],
                    "lastname" => [
                        "type" => "string",
                    ],
                    "street" => [
                        "type" => "string",
                    ],
                    "housenumber" => [
                        "type" => "string",
                    ],
                    "postalcode" => [
                        "type" => "string",
                    ],
                    "city" => [
                        "type" => "string",
                    ],
                    "phone" => [
                        "type" => "string",
                        "format" => "phone",
                    ],
                    "mobile" => [
                        "type" => "string",
                        "format" => "phone",
                    ],
                    "email" => [
                        "type" => "string",
                        "format" => "email",
                    ],
                ],
            ],
        ],
    ],
    "uischema" => [
        "type" => "VerticalLayout",
        "elements" => [
            [
                "label" => "Field group Legend",
                "type" => "Group",
                "options" => [
                    "hideLabel" => true,
                ],
                "elements" => [
                    [
                        "type" => "Group",
                        "options" => [],
                        "elements" => [
                            [
                                "type" => "Control",
                                "label" => "Salutation",
                                "scope" => "#/properties/contact/properties/salutation",
                                "options" => [
                                    "format" => "radio",
                                ],
                            ],
                            [
                                "type" => "HorizontalLayout",
                                "elements" => [
                                    [
                                        "type" => "Control",
                                        "label" => "Firstname",
                                        "scope" => "#/properties/contact/properties/firstname",
                                        "options" => [
                                            "autocomplete" => "given-name",
                                        ],
                                    ],
                                    [
                                        "type" => "Control",
                                        "label" => "Lastname",
                                        "scope" => "#/properties/contact/properties/lastname",
                                        "options" => [
                                            "autocomplete" => "family-name",
                                        ],
                                    ],
                                ],
                            ],
                            [
                                "type" => "HorizontalLayout",
                                "elements" => [
                                    [
                                        "type" => "Control",
                                        "label" => "Street",
                                        "scope" => "#/properties/contact/properties/street",
                                        "options" => [
                                            "autocomplete" => "address-line1",
                                        ],
                                    ],
                                    [
                                        "type" => "Control",
                                        "label" => "Housenumber",
                                        "scope" => "#/properties/contact/properties/housenumber",
                                        "options" => [
                                            "autocomplete" => "on",
                                        ],
                                    ],
                                ],
                            ],
                            [
                                "type" => "HorizontalLayout",
                                "elements" => [
                                    [
                                        "type" => "Control",
                                        "label" => "Postalcode",
                                        "scope" => "#/properties/contact/properties/postalcode",
                                        "options" => [
                                            "autocomplete" => "postal-code",
                                        ],
                                    ],
                                    [
                                        "type" => "Control",
                                        "label" => "City",
                                        "scope" => "#/properties/contact/properties/city",
                                        "options" => [
                                            "autocomplete" => "address-level2",
                                        ],
                                    ],
                                ],
                            ],
                            [
                                "type" => "Control",
                                "label" => "Phone",
                                "scope" => "#/properties/contact/properties/phone",
                                "options" => [
                                    "autocomplete" => "tel-national",
                                ],
                            ],
                            [
                                "type" => "Control",
                                "label" => "Mobile",
                                "scope" => "#/properties/contact/properties/mobile",
                                "options" => [
                                    "autocomplete" => "on",
                                ],
                            ],
                            [
                                "type" => "Control",
                                "label" => "Email",
                                "scope" => "#/properties/contact/properties/email",
                                "options" => [
                                    "autocomplete" => "email",
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
    "data" => [
        'contact' => [
            'salutation' => 'salutationMale',
            'firstname' => 'Peter',
            'lastname' => 'Pan',
            'street' => 'Dreamstreet',
            'housenumber' => '1',
            'postalcode' => '12345',
            'city' => 'Neverland',
            'phone' => '12345',
            'mobile' => '67890',
            'email' => 'pan@neverland.com',
        ],
    ],
    "includeEmtpyFields" => true,
    "expected" => [
        [
            'type' => 'vertical_layout',
            'layout' => true,
            'items' => [
                [
                    'type' => 'group',
                    'layout' => true,
                    'items' => [
                        [
                            'type' => 'group',
                            'layout' => true,
                            'items' => [
                                [
                                    'type' => 'text',
                                    'name' => 'salutation',
                                    'label' => 'Salutation',
                                    'value' => 'salutationMale',
                                ],
                                [
                                    'type' => 'horizontal_layout',
                                    'layout' => true,
                                    'items' => [
                                        [
                                            'type' => 'text',
                                            'name' => 'firstname',
                                            'label' => 'Firstname',
                                            'value' => 'Peter',
                                        ],
                                        [
                                            'type' => 'text',
                                            'name' => 'lastname',
                                            'label' => 'Lastname',
                                            'value' => 'Pan',
                                        ],
                                    ],
                                ],
                                [
                                    'type' => 'horizontal_layout',
                                    'layout' => true,
                                    'items' => [
                                        [
                                            'type' => 'text',
                                            'name' => 'street',
                                            'label' => 'Street',
                                            'value' => 'Dreamstreet',
                                        ],
                                        [
                                            'type' => 'text',
                                            'name' => 'housenumber',
                                            'label' => 'Housenumber',
                                            'value' => '1',
                                        ],
                                    ],
                                ],
                                [
                                    'type' => 'horizontal_layout',
                                    'layout' => true,
                                    'items' => [
                                        [
                                            'type' => 'text',
                                            'name' => 'postalcode',
                                            'label' => 'Postalcode',
                                            'value' => '12345',
                                        ],
                                        [
                                            'type' => 'text',
                                            'name' => 'city',
                                            'label' => 'City',
                                            'value' => 'Neverland',
                                        ],
                                    ],
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'phone',
                                    'label' => 'Phone',
                                    'value' => '12345',
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'mobile',
                                    'label' => 'Mobile',
                                    'value' => '67890',
                                ],
                                [
                                    'type' => 'text',
                                    'name' => 'email',
                                    'label' => 'Email',
                                    'value' => 'pan@neverland.com',
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ],
    ],
];
