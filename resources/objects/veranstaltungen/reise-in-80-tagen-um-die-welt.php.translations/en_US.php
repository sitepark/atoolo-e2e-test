<?php
/* Bootstrap */
if (!isset($context)) {
    $context = include(__DIR__ . '/../../../WEB-IES/sitekit-module/php/bootstrapper.php');
}
if (!isset($lifecycle)) {
    $lifecycle = $context->getAttribute('lifecycle');
}

/* Lifecycle-Process */
$resource = $lifecycle->init([
    "id" => 34526,
    "version" => "1670935469267",
    "encoding" => "UTF-8",
    "locale" => "en_US",
    "objectType" => "eventsCalendar-event",
    "url" => "/veranstaltungen/reise-in-80-tagen-um-die-welt.php",
    "created" => 1670935469,
    "changed" => 1670935469,
    "generated" => 1711458935,
    "ies" => [
        "id" => "100560100000034526-1015",
        "application" => "infosite6",
    ],
    "name" => "Around the World in 80 Days",
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
            "id" => 1129,
            "groupType" => "moduleContentGroup"
        ],
        [
            "id" => 1451,
            "groupType" => "eventsCalendarGroup"
        ],
        [
            "id" => 1454,
            "groupType" => "eventsCalendar-eventRootGroup"
        ],
        [
            "id" => 1455,
            "groupType" => "eventsCalendar-eventGroup"
        ],
        [
            "id" => 34526,
            "groupType" => null
        ]
    ],
    "contentSectionTypes" => [
        "text"
    ]
]);
if ($lifecycle->finish($resource)) { return $resource; }

if ($lifecycle->process("base", $resource)) { $resource->process("base", [
    "date" => 1670935468,
    "title" => "Around the World in 80 Days",
    "kicker" => "Stage / Theater / Performance",
    "teaser" => [
        "date" => 1670935468,
        "headline" => "Around the World in 80 Days",
        "text" => "The play \"Around the World in 80 Days\" will be performed as part of the \"Afternoon Culture\" series at Theater La Lune."
    ],
    "kickerNotice" => null,
    "kickerAddOn" => null,
    "trees" => [],
    "archive" => false
]); }
if ($lifecycle->finish($resource)) { return $resource; }

if ($lifecycle->process("metadata", $resource)) { $resource->process("metadata", [
    "headline" => "Around the World in 80 Days",
    "intro" => "The play \"Around the World in 80 Days\" will be performed as part of the \"Afternoon Culture\" series at Theater La Lune.",
    "description" => "The play \"Around the World in 80 Days\" will be performed as part of the \"Afternoon Culture\" series at Theater La Lune.",
    "scheduling" => [[
        "scheduleId" => "100560100000034526-1015:sp_scheduling_iterate[0]",
        "scheduleType" => "single",
        "fullDay" => false,
        "contentType" => "schedule schedule_single schedule_start schedule_end",
        "from" => 2372511600,
        "to" => 2372543999,
        "hasBeginTime" => true,
        "hasEndTime" => true,
        "status" => "expired"
    ]],
    "schedulingRaw" => [[
        "type" => "single",
        "beginDate" => 2372454000,
        "isFullDay" => false,
        "beginTime" => "16:00",
        "endTime" => "23:59",
        "scheduleId" => "100560100000034526-1015:sp_scheduling_iterate[0]",
        "exceptions" => null
    ]],
    "categories" => [
        [
            "id" => 16589,
            "name" => "Theater",
            "objectType" => "category",
            "url" => "/kategorien/theater.php"
        ],
        [
            "id" => 14169,
            "name" => "03 Seniors",
            "objectType" => "category",
            "url" => "/kategorien/zielgruppen/03-senioren.php"
        ],
        [
            "id" => 32198,
            "name" => "Performance",
            "objectType" => "category",
            "url" => "/kategorien/auffuehrung.php"
        ]
    ],
    "categoriesPath" => [
        [
            "id" => 16589,
            "name" => "Theater",
            "objectType" => "category",
            "url" => "/kategorien/theater.php"
        ],
        [
            "id" => 15697,
            "name" => "Stage",
            "objectType" => "category",
            "url" => "/kategorien/buehne.php"
        ],
        [
            "id" => 14028,
            "name" => "* Section",
            "objectType" => "category",
            "url" => "/kategorien/rubrik.php"
        ],
        [
            "id" => 14169,
            "name" => "03 Seniors",
            "objectType" => "category",
            "url" => "/kategorien/zielgruppen/03-senioren.php"
        ],
        [
            "id" => 14156,
            "name" => "* Target Groups",
            "objectType" => "category",
            "url" => "/kategorien/zielgruppen/zielgruppen.php"
        ],
        [
            "id" => 32198,
            "name" => "Performance",
            "objectType" => "category",
            "url" => "/kategorien/auffuehrung.php"
        ],
        [
            "id" => 32197,
            "name" => "* Type",
            "objectType" => "category",
            "url" => "/kategorien/art.php"
        ]
    ]
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
        "items" => [
            [
                "type" => "implicitSection",
                "model" => [
                    "implicit" => true
                ],
                "id" => "implicitSection-1",
                "items" => [[
                    "type" => "text",
                    "id" => "text-1",
                    "model" => [
                        "modelType" => "content.text",
                        "richText" => [
                            "normalized" => true,
                            "modelType" => "html.richText",
                            "text" => "<p>Verne's novel is a founding myth of globalization and a fast-paced adventure story. Phileas Fogg, an English gentleman par excellence, makes a bet to travel around the world in 80 days. In 1872, an impossible mission. Accompanied by his servant Passepartout, he sets out on the journey, followed by Detective Mr. Fix, who believes Phileas Fogg is a bank robber and wants to put him behind bars as quickly as possible. From the salon of Theater La Lune, we follow Phileas Fogg's wild journey against time around the world and report with wonderful images from the adventurous places Fogg passes through.</p><p>Starring: Robert Atzlinger, Julianna Herzberg, and Semjon E. Dolmetsch * Costumes: Marie Freihofer and Sonja Hoyler * Directed by: Dieter Nelle</p>"
                        ]
                    ]
                ]]
            ]
        ]
    ]]
]); }
if ($lifecycle->finish($resource)) { return $resource; }

return $lifecycle->service($resource);
