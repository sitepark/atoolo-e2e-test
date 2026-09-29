<?php
/* Bootstrap */
if (!isset($context)) {
	$context = include(__DIR__ . '/../../../WEB-IES/sitekit-module/php/bootstrapper.php');
}
if (!isset($lifecycle)) {
	$lifecycle = $context->getAttribute('lifecycle');
}

/* Lifecylce-Process */
$resource = $lifecycle->init([
	"id" => 16589,
	"version" => "1670935469267",
	"encoding" => "UTF-8",
	"locale" => "en_US",
	"objectType" => "category",
	"url" => "/kategorien/theater.php",
	"created" => 1670935469,
	"changed" => 1670935469,
	"generated" => 1711458935,
	"ies" => [
		"id" => "100560100000016589-1015",
		"application" => "infosite6"
	],
	"name" => "Theatre",
	"groupPath" => [
		[
			"id" => 1002,
			"groupType" => null
		],
		[
			"id" => 16589,
			"groupType" => null
		]
	],
	"contentSectionTypes" => [
	]
]);
if ($lifecycle->finish($resource)) { return $resource; }

if ($lifecycle->process("base", $resource)) { $resource->process("base", [
	"title" => "Theatre"
]);}
if ($lifecycle->finish($resource)) { return $resource; }

if ($lifecycle->process("content", $resource)) { $resource->process("content", [
]); }
if ($lifecycle->finish($resource)) { return $resource; }

return $lifecycle->service($resource);
