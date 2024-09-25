<?php
/* Bootstrap */
if (!isset($context)) {
    $context = include(__DIR__ . '/./WEB-IES/sitekit-module/php/bootstrapper.php');
}
if (!isset($lifecycle)) {
    $lifecycle = $context->getAttribute('lifecycle');
}

$resource = $context->redirectToTranslation($lifecycle, '/index.php');
if ($resource !== null) {
    return $resource;
}

/* Lifecylce-Process */
$resource = $lifecycle->init([
    "id" => 8204,
    "version" => "1726581655288",
    "encoding" => "UTF-8",
    "locale" => "de_DE",
    "objectType" => "content",
    "url" => "/forms/jsonForms.php",
    "created" => 1725528628,
    "changed" => 1726581655,
    "generated" => 1726648665,
    "ies" => [
        "id" => "100010100000008204-1015",
        "application" => "infosite6"
    ],
    "name" => "JSONForm Form",
    "siteNature" => "internetroot",
    "site" => "internetwebsite",
    "siteGroup" => [
        "id" => 1117,
        "anchor" => "internetwebsite",
        "name" => "A) Website DE",
        "groupType" => "siteGroup"
    ],
    "changedBy" => [
        "id" => "100560100000001744-3001",
        "lastName" => "Veltrup",
        "firstName" => "Holger"
    ],
    "groupPath" => [
        [
            "id" => 1002,
            "groupType" => null
        ],
        [
            "id" => 1006,
            "groupType" => "commonGroup"
        ],
        [
            "id" => 1116,
            "groupType" => "commonGroup"
        ],
        [
            "id" => 1117,
            "groupType" => "siteGroup"
        ],
        [
            "id" => 1225,
            "groupType" => "contentGroup"
        ],
        [
            "id" => 8204,
            "groupType" => null
        ]
    ],
    "contentSectionTypes" => [
    ]
]);
if ($lifecycle->finish($resource)) { return $resource; }

if ($lifecycle->process("base", $resource)) { $resource->process("base", [
    "date" => 1725528627,
    "publishDate" => 0,
    "teaser" => [
        "date" => 1725528627,
        "headline" => "JSONForm Form"
    ],
    "title" => "JSONForm Form",
    "prefetch" => [],
    "startletter" => "j"
]);}
if ($lifecycle->finish($resource)) { return $resource; }

if ($lifecycle->process("metadata", $resource)) { $resource->process("metadata", [
    "headline" => "JSONForm Form"
]); }
if ($lifecycle->finish($resource)) { return $resource; }

if ($lifecycle->process("searchindexdata", $resource)) { $resource->process("searchindexdata", [
    "content" => ""
]); }
if ($lifecycle->finish($resource)) { return $resource; }

if ($lifecycle->process("content", $resource)) { $resource->process("content", [
    "type" => "ROOT",
    "id" => "ROOT",
    "items" => [[
        "type" => "main",
        "id" => "main",
        "items" => [[
            "type" => "implicitSection",
            "model" => [
                "implicit" => true
            ],
            "id" => "implicitSection-1",
            "items" => [
                [
                    "type" => "fhms-whinchat.text",
                    "id" => "text-1",
                    "model" => [
                        "modelType" => "content.text",
                        "richText" => [
                            "normalized" => true,
                            "modelType" => "html.richText",
                            "text" => "<p>aa vbv</p>"
                        ],
                        "headline" => "text",
                        "doubleColumns" => false
                    ]
                ],
                [
                    "type" => "formContainer",
                    "id" => "formEditor-1",
                    "model" => [
                        "headline" => "Formular-Überschrift",
                        "https" => true,
                        "consent" => false,
                        "items" => [
                            [
                                "id" => "fieldset-1",
                                "type" => "fieldset",
                                "legend" => "Feldgruppe Legende",
                                "hideLegend" => true,
                                "items" => [
                                    [
                                        "id" => "field-1",
                                        "name" => "field-1",
                                        "label" => "Einzeiliges Textfeld",
                                        "autocomplete" => "name",
                                        "description" => null,
                                        "type" => "field.text",
                                        "required" => true,
                                        "spaceAfter" => true,
                                        "validators" => [[
                                            "type" => "required",
                                            "messages" => [
                                                "required" => "Das einzeiliges Textfeld muss angegeben werden"
                                            ]
                                        ]]
                                    ],
                                    [
                                        "id" => "field-2",
                                        "name" => "field-2",
                                        "label" => "Dateupload",
                                        "description" => null,
                                        "type" => "field.file",
                                        "required" => false,
                                        "spaceAfter" => true,
                                        "validators" => [
                                            [
                                                "type" => "fileExtension",
                                                "includes" => [
                                                    "png",
                                                    "jpg"
                                                ],
                                                "messages" => [
                                                    "fileExtension" => "Diese Datei ist nicht gütig"
                                                ]
                                            ],
                                            [
                                                "type" => "fileSize",
                                                "max" => 2000000,
                                                "messages" => [
                                                    "maxFileSize" => "Diese Datei ist nicht gütig"
                                                ]
                                            ],
                                            [
                                                "type" => "fileMimeType",
                                                "includes" => ["image/*"],
                                                "messages" => [
                                                    "fileMimeType" => "Diese Datei ist nicht gütig"
                                                ]
                                            ]
                                        ]
                                    ],
                                    [
                                        "id" => "field-3",
                                        "name" => "field-3",
                                        "richTextLabel" => [
                                            "entities" => [[
                                                "start" => 45,
                                                "link" => [
                                                    "resourceUrl" => "/rubrik/studiengang.php",
                                                    "modelType" => "content.link.link",
                                                    "label" => "Studiengang",
                                                    "url" => "/rubrik/studiengang.php"
                                                ],
                                                "end" => 87,
                                                "modelType" => "html.richText.internalLink",
                                                "inner" => [
                                                    "normalized" => true,
                                                    "modelType" => "html.richText",
                                                    "text" => "Link"
                                                ]
                                            ]],
                                            "normalized" => true,
                                            "modelType" => "html.richText",
                                            "text" => "Anmerkung mit&nbsp;<strong>html</strong> und <a href=\"/rubrik/studiengang.php\">Link</a>."
                                        ],
                                        "description" => null,
                                        "type" => "field.annotation",
                                        "required" => false,
                                        "spaceAfter" => false
                                    ],
                                    [
                                        "id" => "field-4",
                                        "name" => "field-4",
                                        "label" => "Email",
                                        "autocomplete" => "email",
                                        "description" => null,
                                        "type" => "field.email",
                                        "asReplyTo" => true,
                                        "required" => false,
                                        "spaceAfter" => false,
                                        "validators" => [[
                                            "type" => "email",
                                            "messages" => [
                                                "email" => "\${sitekit.form.validatorRule.messages.email}"
                                            ]
                                        ]],
                                        "confirmation" => false
                                    ],
                                    [
                                        "id" => "field-5",
                                        "name" => "field-5",
                                        "label" => "Telefonnummer",
                                        "autocomplete" => "tel",
                                        "description" => null,
                                        "type" => "field.tel",
                                        "required" => false,
                                        "spaceAfter" => false
                                    ],
                                    [
                                        "id" => "field-6",
                                        "name" => "field-6",
                                        "label" => "Zahlen",
                                        "autocomplete" => "off",
                                        "description" => null,
                                        "type" => "field.number",
                                        "required" => false,
                                        "spaceAfter" => false,
                                        "validators" => [[
                                            "type" => "number",
                                            "decimal" => false,
                                            "messages" => [
                                                "integer" => null
                                            ]
                                        ]]
                                    ],
                                    [
                                        "id" => "field-7",
                                        "name" => "field-7",
                                        "label" => "Datum",
                                        "autocomplete" => "name",
                                        "description" => null,
                                        "type" => "field.date",
                                        "required" => false,
                                        "spaceAfter" => false
                                    ],
                                    [
                                        "id" => "field-8",
                                        "name" => "field-8",
                                        "label" => "mehrzeiliges Textfeld",
                                        "autocomplete" => "off",
                                        "description" => null,
                                        "type" => "field.textarea",
                                        "required" => false,
                                        "spaceAfter" => false
                                    ],
                                    [
                                        "id" => "field-9",
                                        "name" => "field-9",
                                        "label" => "Richtext eingabefeld",
                                        "description" => null,
                                        "type" => "field.richText",
                                        "required" => false,
                                        "spaceAfter" => false,
                                        "allowedElements" => [
                                            "p",
                                            "strong",
                                            "em",
                                            "li",
                                            "ul",
                                            "ol"
                                        ]
                                    ],
                                    [
                                        "id" => "field-10",
                                        "name" => "field-10",
                                        "label" => "Checkbox",
                                        "description" => null,
                                        "type" => "field.checkbox",
                                        "required" => false,
                                        "spaceAfter" => false,
                                        "value" => "true"
                                    ],
                                    [
                                        "id" => "field-11",
                                        "name" => "field-11",
                                        "richTextLabel" => [
                                            "entities" => [[
                                                "start" => 36,
                                                "link" => [
                                                    "resourceUrl" => "/rubrik/studiengang.php",
                                                    "modelType" => "content.link.link",
                                                    "label" => "Studiengang",
                                                    "url" => "/rubrik/studiengang.php"
                                                ],
                                                "end" => 78,
                                                "modelType" => "html.richText.internalLink",
                                                "inner" => [
                                                    "normalized" => true,
                                                    "modelType" => "html.richText",
                                                    "text" => "Link"
                                                ]
                                            ]],
                                            "normalized" => true,
                                            "modelType" => "html.richText",
                                            "text" => "Ein <strong>Html</strong> label mit <a href=\"/rubrik/studiengang.php\">Link</a>."
                                        ],
                                        "description" => null,
                                        "type" => "field.annotatedCheckbox",
                                        "required" => false,
                                        "spaceAfter" => false,
                                        "value" => "true",
                                        "label" => "Checkbox mit Richt-Text-Label"
                                    ],
                                    [
                                        "id" => "field-12",
                                        "name" => "field-12",
                                        "label" => "Checkbox-Gruppe",
                                        "description" => null,
                                        "type" => "field.checkboxGroup",
                                        "required" => false,
                                        "spaceAfter" => false,
                                        "items" => [
                                            [
                                                "type" => "field.checkbox",
                                                "id" => "option-1",
                                                "label" => "Hund",
                                                "value" => "ad5bf874-90b4-4e97-8aaa-2dde2622e918"
                                            ],
                                            [
                                                "type" => "field.checkbox",
                                                "id" => "option-2",
                                                "label" => "Katze",
                                                "value" => "28ae045f-557b-48d9-9d68-eb62f49c34d7"
                                            ],
                                            [
                                                "type" => "field.checkbox",
                                                "id" => "option-3",
                                                "label" => "Maus",
                                                "value" => "598b8184-80e7-43f2-b6cd-982ca22f6cf0"
                                            ]
                                        ]
                                    ],
                                    [
                                        "id" => "field-13",
                                        "name" => "field-13",
                                        "label" => "Radio-Buttons",
                                        "description" => null,
                                        "type" => "field.radioGroup",
                                        "required" => false,
                                        "spaceAfter" => false,
                                        "items" => [
                                            [
                                                "type" => "field.radio",
                                                "id" => "option-4",
                                                "label" => "Hund",
                                                "value" => "e78099f0-1b5f-40e3-8fa1-eb71247afe11"
                                            ],
                                            [
                                                "type" => "field.radio",
                                                "id" => "option-5",
                                                "label" => "Katze",
                                                "value" => "8cd05a5b-cbfe-4f49-bcf5-f38a5409f7aa"
                                            ],
                                            [
                                                "type" => "field.radio",
                                                "id" => "option-6",
                                                "label" => "Maus",
                                                "value" => "6b58b515-6626-4b5a-b1e1-4965a9d38e80"
                                            ]
                                        ]
                                    ],
                                    [
                                        "id" => "field-14",
                                        "name" => "field-14",
                                        "label" => "Auswahlliste",
                                        "description" => null,
                                        "type" => "field.select",
                                        "required" => false,
                                        "spaceAfter" => false,
                                        "options" => [
                                            [
                                                "type" => "field.option",
                                                "id" => "option-7",
                                                "label" => "Hund",
                                                "value" => "7384956d-a19d-4967-88e2-3d3c900d5214"
                                            ],
                                            [
                                                "type" => "field.option",
                                                "id" => "option-8",
                                                "label" => "Katze",
                                                "value" => "0a2e796f-3db9-449b-8861-6e15c11778a0"
                                            ],
                                            [
                                                "type" => "field.option",
                                                "id" => "option-9",
                                                "label" => "Maus",
                                                "value" => "843024f7-2f5a-48a0-9f88-2a9c4aa30d18"
                                            ]
                                        ]
                                    ],
                                    [
                                        "id" => "field-15",
                                        "name" => "contact",
                                        "description" => null,
                                        "type" => "fieldset",
                                        "spaceAfter" => false,
                                        "items" => [
                                            [
                                                "modelType" => "content.form.field.salutation",
                                                "type" => "field.salutation",
                                                "id" => "salutation-1",
                                                "name" => "salutation-1",
                                                "label" => "\${field.salutation.label}",
                                                "required" => false,
                                                "options" => [
                                                    "salutationFemale",
                                                    "salutationMale",
                                                    "salutationDiverse",
                                                    "salutationNotSpecified"
                                                ]
                                            ],
                                            [
                                                "type" => "field.firstnameLastnameComposition",
                                                "id" => "name-1",
                                                "firstname" => [
                                                    "modelType" => "content.form.field.firstname",
                                                    "type" => "field.firstname",
                                                    "id" => "firstname-1",
                                                    "name" => "firstname-1",
                                                    "label" => "\${field.firstname.label}",
                                                    "required" => false,
                                                    "autocomplete" => "given-name"
                                                ],
                                                "lastname" => [
                                                    "modelType" => "content.form.field.lastname",
                                                    "type" => "field.lastname",
                                                    "id" => "lastname-1",
                                                    "name" => "lastname-1",
                                                    "label" => "\${field.lastname.label}",
                                                    "required" => false,
                                                    "autocomplete" => "family-name"
                                                ]
                                            ],
                                            [
                                                "type" => "field.streetHousenumberComposition",
                                                "id" => "address-1",
                                                "street" => [
                                                    "modelType" => "content.form.field.street",
                                                    "type" => "field.street",
                                                    "id" => "street-1",
                                                    "name" => "street-1",
                                                    "label" => "\${field.street.label}",
                                                    "required" => false,
                                                    "autocomplete" => "address-line1"
                                                ],
                                                "housenumber" => [
                                                    "modelType" => "content.form.field.housenumber",
                                                    "type" => "field.housenumber",
                                                    "id" => "housenumber-1",
                                                    "name" => "housenumber-1",
                                                    "label" => "\${field.housenumber.label}",
                                                    "required" => false,
                                                    "autocomplete" => "on"
                                                ]
                                            ],
                                            [
                                                "type" => "field.postalCodeCityComposition",
                                                "id" => "city-1",
                                                "postalCode" => [
                                                    "modelType" => "content.form.field.postalCode",
                                                    "type" => "field.postalCode",
                                                    "id" => "postalCode-1",
                                                    "name" => "postalCode-1",
                                                    "label" => "\${field.postalCode.label}",
                                                    "required" => false,
                                                    "autocomplete" => "postal-code"
                                                ],
                                                "city" => [
                                                    "modelType" => "content.form.field.city",
                                                    "type" => "field.city",
                                                    "id" => "city-2",
                                                    "name" => "city-2",
                                                    "label" => "\${field.city.label}",
                                                    "required" => false,
                                                    "autocomplete" => "address-level2"
                                                ]
                                            ],
                                            [
                                                "modelType" => "content.form.field.tel",
                                                "type" => "field.tel",
                                                "id" => "phone-1",
                                                "name" => "phone-1",
                                                "label" => "\${field.tel.label}",
                                                "required" => false,
                                                "autocomplete" => "tel-national"
                                            ],
                                            [
                                                "modelType" => "content.form.field.tel",
                                                "type" => "field.tel",
                                                "id" => "mobile-1",
                                                "name" => "mobile-1",
                                                "label" => "\${field.mobile.label}",
                                                "required" => false,
                                                "autocomplete" => "on"
                                            ],
                                            [
                                                "modelType" => "content.form.field.email",
                                                "type" => "field.email",
                                                "id" => "email-1",
                                                "name" => "email-1",
                                                "label" => "\${field.email.label}",
                                                "confirmation" => false,
                                                "required" => false,
                                                "autocomplete" => "email"
                                            ]
                                        ]
                                    ],
                                    [
                                        "id" => "field-16",
                                        "name" => "contact",
                                        "description" => null,
                                        "type" => "webAccountContact",
                                        "spaceAfter" => false,
                                        "items" => [
                                            [
                                                "type" => "field.webAccount.firstnameLastnameComposition",
                                                "id" => "name-2",
                                                "firstname" => [
                                                    "modelType" => "content.form.field.webAccount.firstname",
                                                    "type" => "field.fwebAccount.irstname",
                                                    "id" => "firstname-2",
                                                    "name" => "firstname-2",
                                                    "label" => "\${field.webAccount.firstname.label}",
                                                    "required" => false,
                                                    "autocomplete" => "given-name"
                                                ],
                                                "lastname" => [
                                                    "modelType" => "content.form.field.webAccount.lastname",
                                                    "type" => "field.webAccount.lastname",
                                                    "id" => "lastname-2",
                                                    "name" => "lastname-2",
                                                    "label" => "\${field.webAccount.lastname.label}",
                                                    "required" => false,
                                                    "autocomplete" => "family-name"
                                                ]
                                            ],
                                            [
                                                "type" => "field.webAccount.streetHousenumberComposition",
                                                "id" => "address-2",
                                                "street" => [
                                                    "modelType" => "content.form.field.webAccount.street",
                                                    "type" => "field.webAccount.street",
                                                    "id" => "street-2",
                                                    "name" => "street-2",
                                                    "label" => "\${field.webAccount.street.label}",
                                                    "required" => false,
                                                    "autocomplete" => "address-line1"
                                                ],
                                                "housenumber" => [
                                                    "modelType" => "content.form.field.webAccount.housenumber",
                                                    "type" => "field.webAccount.housenumber",
                                                    "id" => "housenumber-2",
                                                    "name" => "housenumber-2",
                                                    "label" => "\${field.webAccount.housenumber.label}",
                                                    "required" => false,
                                                    "autocomplete" => "on"
                                                ]
                                            ],
                                            [
                                                "type" => "field.webAccount.postalCodeCityComposition",
                                                "id" => "city-3",
                                                "postalCode" => [
                                                    "modelType" => "content.form.field.webAccount.postalCode",
                                                    "type" => "field.webAccount.postalCode",
                                                    "id" => "postalCode-2",
                                                    "name" => "postalCode-2",
                                                    "label" => "\${field.webAccount.postalCode.label}",
                                                    "required" => false,
                                                    "autocomplete" => "postal-code"
                                                ],
                                                "city" => [
                                                    "modelType" => "content.form.field.webAccount.city",
                                                    "type" => "field.webAccount.city",
                                                    "id" => "city-4",
                                                    "name" => "city-4",
                                                    "label" => "\${field.webAccount.city.label}",
                                                    "required" => false,
                                                    "autocomplete" => "address-level2"
                                                ]
                                            ],
                                            [
                                                "modelType" => "content.form.field.webAccount.tel",
                                                "type" => "field.webAccount.tel",
                                                "id" => "phone-2",
                                                "name" => "phone-2",
                                                "label" => "\${field.webAccount.tel.label}",
                                                "required" => false,
                                                "autocomplete" => "tel-national"
                                            ],
                                            [
                                                "modelType" => "content.form.field.webAccount.tel",
                                                "type" => "field.webAccount.tel",
                                                "id" => "mobile-2",
                                                "name" => "mobile-2",
                                                "label" => "\${field.webAccount.mobile.label}",
                                                "required" => false,
                                                "autocomplete" => "on"
                                            ],
                                            [
                                                "modelType" => "content.form.field.webAccount.email",
                                                "type" => "field.webAccount.email",
                                                "id" => "email-2",
                                                "name" => "email-2",
                                                "label" => "\${field.webAccount.email.label}",
                                                "confirmation" => false,
                                                "asReplyTo" => false,
                                                "required" => false,
                                                "autocomplete" => "email"
                                            ]
                                        ]
                                    ]
                                ]
                            ],
                            [
                                "id" => "fieldset-2",
                                "type" => "fieldset",
                                "legend" => "Zweite Gruppe",
                                "hideLegend" => false,
                                "items" => [
                                    [
                                        "id" => "field-17",
                                        "name" => "field-17",
                                        "label" => "Feld in anderer Gruppe",
                                        "autocomplete" => "name",
                                        "description" => null,
                                        "type" => "field.text",
                                        "required" => false,
                                        "spaceAfter" => false
                                    ],
                                    [
                                        "type" => "field.annotation",
                                        "id" => "field-18",
                                        "value" => "weitere Hinweise"
                                    ]
                                ]
                            ]
                        ],
                        "jsonForms" => [
                            "schema" => [
                                "type" => "object",
                                "properties" => [
                                    "field-1" => [
                                        "type" => "string",
                                        "title" => "Einzeiliges Textfeld"
                                    ],
                                    "field-2" => [
                                        "type" => "string",
                                        "title" => "Dateupload",
                                        "acceptedFileNames" => [
                                            "*.png",
                                            "*.jpg"
                                        ],
                                        "maxFileSize" => 2000000,
                                        "acceptedContentTypes" => ["image/*"],
                                        "format" => "data-url"
                                    ],
                                    "field-4" => [
                                        "type" => "string",
                                        "title" => "Email",
                                        "format" => "email"
                                    ],
                                    "field-5" => [
                                        "type" => "string",
                                        "title" => "Telefonnummer",
                                        "format" => "phone"
                                    ],
                                    "field-6" => [
                                        "type" => "integer",
                                        "title" => "Zahlen"
                                    ],
                                    "field-7" => [
                                        "type" => "string",
                                        "title" => "Datum",
                                        "format" => "date"
                                    ],
                                    "field-8" => [
                                        "type" => "string",
                                        "title" => "mehrzeiliges Textfeld"
                                    ],
                                    "field-9" => [
                                        "type" => "string",
                                        "title" => "Richtext eingabefeld",
                                        "format" => "html",
                                        "allowedElements" => [
                                            "p",
                                            "strong",
                                            "em",
                                            "li",
                                            "ul",
                                            "ol"
                                        ]
                                    ],
                                    "field-10" => [
                                        "type" => "boolean",
                                        "title" => "Checkbox"
                                    ],
                                    "field-11" => [
                                        "type" => "boolean"
                                    ],
                                    "field-12" => [
                                        "type" => "array",
                                        "title" => "Checkbox-Gruppe",
                                        "items" => [
                                            "oneOf" => [
                                                [
                                                    "const" => "ad5bf874-90b4-4e97-8aaa-2dde2622e918",
                                                    "title" => "Hund"
                                                ],
                                                [
                                                    "const" => "28ae045f-557b-48d9-9d68-eb62f49c34d7",
                                                    "title" => "Katze"
                                                ],
                                                [
                                                    "const" => "598b8184-80e7-43f2-b6cd-982ca22f6cf0",
                                                    "title" => "Maus"
                                                ]
                                            ]
                                        ],
                                        "uniqueItems" => true
                                    ],
                                    "field-13" => [
                                        "type" => "string",
                                        "title" => "Radio-Buttons",
                                        "oneOf" => [
                                            [
                                                "const" => "e78099f0-1b5f-40e3-8fa1-eb71247afe11",
                                                "title" => "Hund"
                                            ],
                                            [
                                                "const" => "8cd05a5b-cbfe-4f49-bcf5-f38a5409f7aa",
                                                "title" => "Katze"
                                            ],
                                            [
                                                "const" => "6b58b515-6626-4b5a-b1e1-4965a9d38e80",
                                                "title" => "Maus"
                                            ]
                                        ]
                                    ],
                                    "field-14" => [
                                        "type" => "string",
                                        "title" => "Auswahlliste",
                                        "oneOf" => [
                                            [
                                                "const" => "7384956d-a19d-4967-88e2-3d3c900d5214",
                                                "title" => "Hund"
                                            ],
                                            [
                                                "const" => "0a2e796f-3db9-449b-8861-6e15c11778a0",
                                                "title" => "Katze"
                                            ],
                                            [
                                                "const" => "843024f7-2f5a-48a0-9f88-2a9c4aa30d18",
                                                "title" => "Maus"
                                            ]
                                        ]
                                    ],
                                    "contact" => [
                                        "type" => "object",
                                        "properties" => [
                                            "salutation" => [
                                                "type" => "string",
                                                "oneOf" => [
                                                    [
                                                        "const" => "salutationFemale",
                                                        "title" => "\${field.salutation.salutationFemale.label}"
                                                    ],
                                                    [
                                                        "const" => "salutationMale",
                                                        "title" => "\${field.salutation.salutationMale.label}"
                                                    ],
                                                    [
                                                        "const" => "salutationDiverse",
                                                        "title" => "\${field.salutation.salutationDiverse.label}"
                                                    ],
                                                    [
                                                        "const" => "salutationNotSpecified",
                                                        "title" => "\${field.salutation.salutationNotSpecified.label}"
                                                    ]
                                                ]
                                            ],
                                            "firstname" => [
                                                "type" => "string"
                                            ],
                                            "lastname" => [
                                                "type" => "string"
                                            ],
                                            "street" => [
                                                "type" => "string"
                                            ],
                                            "housenumber" => [
                                                "type" => "string"
                                            ],
                                            "postalcode" => [
                                                "type" => "string"
                                            ],
                                            "city" => [
                                                "type" => "string"
                                            ],
                                            "phone" => [
                                                "type" => "string",
                                                "format" => "phone"
                                            ],
                                            "mobile" => [
                                                "type" => "string",
                                                "format" => "phone"
                                            ],
                                            "email" => [
                                                "type" => "string",
                                                "format" => "email"
                                            ]
                                        ]
                                    ],
                                    "webAccountContact" => [
                                        "type" => "object",
                                        "properties" => [
                                            "firstname" => [
                                                "type" => "string"
                                            ],
                                            "lastname" => [
                                                "type" => "string"
                                            ],
                                            "street" => [
                                                "type" => "string"
                                            ],
                                            "housenumber" => [
                                                "type" => "string"
                                            ],
                                            "postalcode" => [
                                                "type" => "string"
                                            ],
                                            "city" => [
                                                "type" => "string"
                                            ],
                                            "phone" => [
                                                "type" => "string",
                                                "format" => "phone"
                                            ],
                                            "mobile" => [
                                                "type" => "string",
                                                "format" => "phone"
                                            ],
                                            "email" => [
                                                "type" => "string",
                                                "format" => "email"
                                            ]
                                        ]
                                    ],
                                    "field-17" => [
                                        "type" => "string",
                                        "title" => "Feld in anderer Gruppe"
                                    ]
                                ],
                                "required" => ["field-1"],
                                "errorMessage" => [
                                    "required" => [
                                        "field-1" => "Das einzeiliges Textfeld muss angegeben werden"
                                    ],
                                    "acceptedFileNames" => [
                                        "field-2" => "Diese Datei ist nicht gütig"
                                    ],
                                    "maxFileSize" => [
                                        "field-2" => "Diese Datei ist nicht gütig"
                                    ],
                                    "acceptedContentTypes" => [
                                        "field-2" => "Diese Datei ist nicht gütig"
                                    ]
                                ]
                            ],
                            "uischema" => [
                                "type" => "VerticalLayout",
                                "elements" => [
                                    [
                                        "label" => "Feldgruppe Legende",
                                        "type" => "Group",
                                        "options" => [
                                            "hideLegend" => true
                                        ],
                                        "elements" => [
                                            [
                                                "type" => "Control",
                                                "scope" => "#/properties/field-1",
                                                "label" => "Einzeiliges Textfeld",
                                                "options" => [
                                                    "autocomplete" => "name",
                                                    "spaceAfter" => true
                                                ]
                                            ],
                                            [
                                                "type" => "Control",
                                                "scope" => "#/properties/field-2",
                                                "label" => "Dateupload",
                                                "options" => [
                                                    "spaceAfter" => true
                                                ]
                                            ],
                                            [
                                                "type" => "Annotation",
                                                "htmlLabel" => [
                                                    "entities" => [[
                                                        "start" => 45,
                                                        "link" => [
                                                            "resourceUrl" => "/rubrik/studiengang.php",
                                                            "modelType" => "content.link.link",
                                                            "label" => "Studiengang",
                                                            "url" => "/rubrik/studiengang.php"
                                                        ],
                                                        "end" => 87,
                                                        "modelType" => "html.richText.internalLink",
                                                        "inner" => [
                                                            "normalized" => true,
                                                            "modelType" => "html.richText",
                                                            "text" => "Link"
                                                        ]
                                                    ]],
                                                    "normalized" => true,
                                                    "modelType" => "html.richText",
                                                    "text" => "Anmerkung mit&nbsp;<strong>html</strong> und <a href=\"/rubrik/studiengang.php\">Link</a>."
                                                ]
                                            ],
                                            [
                                                "type" => "Control",
                                                "scope" => "#/properties/field-4",
                                                "label" => "Email",
                                                "options" => [
                                                    "autocomplete" => "email",
                                                    "asReplyTo" => true
                                                ]
                                            ],
                                            [
                                                "type" => "Control",
                                                "scope" => "#/properties/field-5",
                                                "label" => "Telefonnummer",
                                                "options" => [
                                                    "autocomplete" => "tel"
                                                ]
                                            ],
                                            [
                                                "type" => "Control",
                                                "scope" => "#/properties/field-6",
                                                "label" => "Zahlen",
                                                "options" => [
                                                    "autocomplete" => "off"
                                                ]
                                            ],
                                            [
                                                "type" => "Control",
                                                "scope" => "#/properties/field-7",
                                                "label" => "Datum",
                                                "options" => [
                                                    "autocomplete" => "name"
                                                ]
                                            ],
                                            [
                                                "type" => "Control",
                                                "scope" => "#/properties/field-8",
                                                "label" => "mehrzeiliges Textfeld",
                                                "options" => [
                                                    "autocomplete" => "off",
                                                    "multi" => true
                                                ]
                                            ],
                                            [
                                                "type" => "Control",
                                                "scope" => "#/properties/field-9",
                                                "label" => "Richtext eingabefeld",
                                                "options" => [
                                                    "htmleditor" => true
                                                ]
                                            ],
                                            [
                                                "type" => "Control",
                                                "scope" => "#/properties/field-10",
                                                "label" => "Checkbox",
                                                "options" => []
                                            ],
                                            [
                                                "type" => "Control",
                                                "scope" => "#/properties/field-11",
                                                "options" => [],
                                                "htmlLabel" => [
                                                    "entities" => [[
                                                        "start" => 36,
                                                        "link" => [
                                                            "resourceUrl" => "/rubrik/studiengang.php",
                                                            "modelType" => "content.link.link",
                                                            "label" => "Studiengang",
                                                            "url" => "/rubrik/studiengang.php"
                                                        ],
                                                        "end" => 78,
                                                        "modelType" => "html.richText.internalLink",
                                                        "inner" => [
                                                            "normalized" => true,
                                                            "modelType" => "html.richText",
                                                            "text" => "Link"
                                                        ]
                                                    ]],
                                                    "normalized" => true,
                                                    "modelType" => "html.richText",
                                                    "text" => "Ein <strong>Html</strong> label mit <a href=\"/rubrik/studiengang.php\">Link</a>."
                                                ]
                                            ],
                                            [
                                                "type" => "Control",
                                                "scope" => "#/properties/field-12",
                                                "label" => "Checkbox-Gruppe",
                                                "options" => []
                                            ],
                                            [
                                                "type" => "Control",
                                                "scope" => "#/properties/field-13",
                                                "label" => "Radio-Buttons",
                                                "options" => [
                                                    "format" => "radio"
                                                ]
                                            ],
                                            [
                                                "type" => "Control",
                                                "scope" => "#/properties/field-14",
                                                "label" => "Auswahlliste",
                                                "options" => []
                                            ],
                                            [
                                                "type" => "Group",
                                                "options" => [],
                                                "elements" => [
                                                    [
                                                        "type" => "Control",
                                                        "label" => "\${field.salutation.label}",
                                                        "scope" => "#/properties/contact/properties/salutation",
                                                        "options" => [
                                                            "format" => "radio"
                                                        ]
                                                    ],
                                                    [
                                                        "type" => "HorizontalLayout",
                                                        "elements" => [
                                                            [
                                                                "type" => "Control",
                                                                "label" => "\${field.firstname.label}",
                                                                "scope" => "#/properties/contact/properties/firstname",
                                                                "options" => [
                                                                    "autocomplete" => "given-name"
                                                                ]
                                                            ],
                                                            [
                                                                "type" => "Control",
                                                                "label" => "\${field.lastname.label}",
                                                                "scope" => "#/properties/contact/properties/lastname",
                                                                "options" => [
                                                                    "autocomplete" => "family-name"
                                                                ]
                                                            ]
                                                        ]
                                                    ],
                                                    [
                                                        "type" => "HorizontalLayout",
                                                        "elements" => [
                                                            [
                                                                "type" => "Control",
                                                                "label" => "\${field.street.label}",
                                                                "scope" => "#/properties/contact/properties/street",
                                                                "options" => [
                                                                    "autocomplete" => "address-line1"
                                                                ]
                                                            ],
                                                            [
                                                                "type" => "Control",
                                                                "label" => "\${field.housenumber.label}",
                                                                "scope" => "#/properties/contact/properties/housenumber",
                                                                "options" => [
                                                                    "autocomplete" => "on"
                                                                ]
                                                            ]
                                                        ]
                                                    ],
                                                    [
                                                        "type" => "HorizontalLayout",
                                                        "elements" => [
                                                            [
                                                                "type" => "Control",
                                                                "label" => "\${field.postalCode.label}",
                                                                "scope" => "#/properties/contact/properties/postalcode",
                                                                "options" => [
                                                                    "autocomplete" => "postal-code"
                                                                ]
                                                            ],
                                                            [
                                                                "type" => "Control",
                                                                "label" => "\${field.city.label}",
                                                                "scope" => "#/properties/contact/properties/city",
                                                                "options" => [
                                                                    "autocomplete" => "address-level2"
                                                                ]
                                                            ]
                                                        ]
                                                    ],
                                                    [
                                                        "type" => "Control",
                                                        "label" => "\${field.tel.label}",
                                                        "scope" => "#/properties/contact/properties/phone",
                                                        "options" => [
                                                            "autocomplete" => "tel-national"
                                                        ]
                                                    ],
                                                    [
                                                        "type" => "Control",
                                                        "label" => "\${field.mobile.label}",
                                                        "scope" => "#/properties/contact/properties/mobile",
                                                        "options" => [
                                                            "autocomplete" => "on"
                                                        ]
                                                    ],
                                                    [
                                                        "type" => "Control",
                                                        "label" => "\${field.email.label}",
                                                        "scope" => "#/properties/contact/properties/email",
                                                        "options" => [
                                                            "autocomplete" => "email"
                                                        ]
                                                    ]
                                                ]
                                            ],
                                            [
                                                "type" => "Group",
                                                "options" => [],
                                                "elements" => [
                                                    [
                                                        "type" => "HorizontalLayout",
                                                        "elements" => [
                                                            [
                                                                "type" => "Control",
                                                                "label" => "\${field.webAccount.firstname.label}",
                                                                "scope" => "#/properties/webAccountContact/properties/firstname",
                                                                "options" => [
                                                                    "autocomplete" => "given-name"
                                                                ]
                                                            ],
                                                            [
                                                                "type" => "Control",
                                                                "label" => "\${field.webAccount.lastname.label}",
                                                                "scope" => "#/properties/webAccountContact/properties/lastname",
                                                                "options" => [
                                                                    "autocomplete" => "family-name"
                                                                ]
                                                            ]
                                                        ]
                                                    ],
                                                    [
                                                        "type" => "HorizontalLayout",
                                                        "elements" => [
                                                            [
                                                                "type" => "Control",
                                                                "label" => "\${field.webAccount.street.label}",
                                                                "scope" => "#/properties/webAccountContact/properties/street",
                                                                "options" => [
                                                                    "autocomplete" => "address-line1"
                                                                ]
                                                            ],
                                                            [
                                                                "type" => "Control",
                                                                "label" => "\${field.webAccount.housenumber.label}",
                                                                "scope" => "#/properties/webAccountContact/properties/housenumber",
                                                                "options" => [
                                                                    "autocomplete" => "on"
                                                                ]
                                                            ]
                                                        ]
                                                    ],
                                                    [
                                                        "type" => "HorizontalLayout",
                                                        "elements" => [
                                                            [
                                                                "type" => "Control",
                                                                "label" => "\${field.webAccount.postalCode.label}",
                                                                "scope" => "#/properties/webAccountContact/properties/postalcode",
                                                                "options" => [
                                                                    "autocomplete" => "postal-code"
                                                                ]
                                                            ],
                                                            [
                                                                "type" => "Control",
                                                                "label" => "\${field.webAccount.city.label}",
                                                                "scope" => "#/properties/webAccountContact/properties/city",
                                                                "options" => [
                                                                    "autocomplete" => "address-level2"
                                                                ]
                                                            ]
                                                        ]
                                                    ],
                                                    [
                                                        "type" => "Control",
                                                        "label" => "\${field.webAccount.tel.label}",
                                                        "scope" => "#/properties/webAccountContact/properties/phone",
                                                        "options" => [
                                                            "autocomplete" => "tel-national"
                                                        ]
                                                    ],
                                                    [
                                                        "type" => "Control",
                                                        "label" => "\${field.webAccount.mobile.label}",
                                                        "scope" => "#/properties/webAccountContact/properties/mobile",
                                                        "options" => [
                                                            "autocomplete" => "on"
                                                        ]
                                                    ],
                                                    [
                                                        "type" => "Control",
                                                        "label" => "\${field.webAccount.email.label}",
                                                        "scope" => "#/properties/webAccountContact/properties/email",
                                                        "options" => [
                                                            "autocomplete" => "email"
                                                        ]
                                                    ]
                                                ]
                                            ]
                                        ]
                                    ],
                                    [
                                        "label" => "Zweite Gruppe",
                                        "type" => "Group",
                                        "options" => [
                                            "hideLegend" => false
                                        ],
                                        "elements" => [[
                                            "type" => "Control",
                                            "scope" => "#/properties/field-17",
                                            "label" => "Feld in anderer Gruppe",
                                            "options" => [
                                                "autocomplete" => "name"
                                            ]
                                        ]]
                                    ],
                                    [
                                        "type" => "Annotation",
                                        "htmlLabel.text" => "weitere Hinweise"
                                    ]
                                ]
                            ]
                        ],
                        "bottomBar" => [
                            "items" => [[
                                "type" => "button.submit",
                                "id" => "button-1",
                                "label" => "absenden",
                                "value" => "submit",
                                "action" => "submit"
                            ]]
                        ],
                        "deliverer" => [
                            "modelType" => "content.form.deliverer.email",
                            "from" => [
                                "e2e@atoolo.com" => "Atoolo"
                            ],
                            "to" => [
                                "2e2@atoolo.com" => "Atoolo"
                            ],
                            "subject" => "Atoolo form test",
                            "type" => "email",
                            "format" => "html",
                            "attachCsv" => false,
                            "showEmpty" => false
                        ],
                        "messages" => [
                            "success" => [
                                "headline" => "Bestätigung",
                                "text" => "Text"
                            ]
                        ]
                    ]
                ]
            ]
        ]]
    ]]
]); }
if ($lifecycle->finish($resource)) { return $resource; }

return $lifecycle->service($resource);

