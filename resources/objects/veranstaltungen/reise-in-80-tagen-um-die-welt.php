<?php
/* Bootstrap */
if (!isset($context)) {
	$context = include(__DIR__ . '/../../../WEB-IES/sitekit-module/php/bootstrapper.php');
}
if (!isset($lifecycle)) {
	$lifecycle = $context->getAttribute('lifecycle');
}

$resource = $context->redirectToTranslation($lifecycle, '/veranstaltungen/reise-in-80-tagen-um-die-welt.php');
if ($resource !== null) {
	return $resource;
}

/* Lifecylce-Process */
$resource = $lifecycle->init([
	"id" => 34526,
	"version" => "1670935469267",
	"encoding" => "UTF-8",
	"locale" => "de_DE",
	"objectType" => "eventsCalendar-event",
	"url" => "/veranstaltungen/reise-in-80-tagen-um-die-welt.php",
	"created" => 1670935469,
	"changed" => 1670935469,
	"generated" => 1711458935,
	"ies" => [
		"id" => "100560100000034526-1015",
		"application" => "infosite6"
	],
	"name" => "Reise in 80 Tagen um die Welt",
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
	"date" => 2372504311,
	"title" => "Reise in 80 Tagen um die Welt",
	"kicker" => "Bühne / Theater / Aufführung",
	"teaser" => [
		"date" => 2372511600,
		"headline" => "Reise in 80 Tagen um die Welt",
		"text" => "Das Stück \"Reise in 80 Tagen um die Welt\" wird im Rahmen der Reihe \"Kultur am Nachmittag\" im Theater La Lune aufgeführt."
	],
	"kickerNotice" => null,
	"kickerAddOn" => null,
	"trees" => [],
	"archive" => false
]); }
if ($lifecycle->finish($resource)) { return $resource; }

if ($lifecycle->process("metadata", $resource)) { $resource->process("metadata", [
	"headline" => "Reise in 80 Tagen um die Welt",
	"intro" => "Das Stück \"Reise in 80 Tagen um die Welt\" wird im Rahmen der Reihe \"Kultur am Nachmittag\" im Theater La Lune aufgeführt.",
	"description" => "Das Stück \"Reise in 80 Tagen um die Welt\" wird im Rahmen der Reihe \"Kultur am Nachmittag\" im Theater La Lune aufgeführt.",
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
			"name" => "03 Senioren",
			"objectType" => "category",
			"url" => "/kategorien/zielgruppen/03-senioren.php"
		],
		[
			"id" => 32198,
			"name" => "Aufführung",
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
			"name" => "Bühne",
			"objectType" => "category",
			"url" => "/kategorien/buehne.php"
		],
		[
			"id" => 14028,
			"name" => "* Rubrik",
			"objectType" => "category",
			"url" => "/kategorien/rubrik.php"
		],
		[
			"id" => 14169,
			"name" => "03 Senioren",
			"objectType" => "category",
			"url" => "/kategorien/zielgruppen/03-senioren.php"
		],
		[
			"id" => 14156,
			"name" => "* Zielgruppen",
			"objectType" => "category",
			"url" => "/kategorien/zielgruppen/zielgruppen.php"
		],
		[
			"id" => 32198,
			"name" => "Aufführung",
			"objectType" => "category",
			"url" => "/kategorien/auffuehrung.php"
		],
		[
			"id" => 32197,
			"name" => "* Art",
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
							"text" => "<p>Vernes Roman ist ein Gründungsmythos der Globalisierung und eine rasante Abenteuergeschichte. Phileas Fogg, ein englischer Gentleman par excellence, schließt eine Wette ab, in 80 Tagen um die Welt zu reisen. 1872 eine Mission impossible. Mit seinem Diener Passpartout macht er sich auf den Weg, gefolgt von dem Detektiv Mr. Fix, der Phileas Fogg für einen Bankräuber hält und ihn möglichst schnell hinter Gitter bringen will. Vom Salon des Theaters La Lune aus verfolgen wir die wilde Reise des Phileas Fogg gegen die Zeit einmal um die Welt und berichten mit wunderbaren Bildausschnitten von den abenteuerlichen Orten, die Fogg passiert.</p><p>Es spielen: Robert Atzlinger, Julianna Herzberg und Semjon E. Dolmetsch * Ausstattung: Marie Freihofer und Sonja Hoyler * Regie: Dieter Nelle</p>"
						]
					]
				]]
			]
		]
	]]
]); }
if ($lifecycle->finish($resource)) { return $resource; }

return $lifecycle->service($resource);

