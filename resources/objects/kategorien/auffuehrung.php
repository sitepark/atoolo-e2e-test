<?php
/* Bootstrap */
if (!isset($context)) {
	$context = include(__DIR__ . '/../../../WEB-IES/sitekit-module/php/bootstrapper.php');
}
if (!isset($lifecycle)) {
	$lifecycle = $context->getAttribute('lifecycle');
}

$resource = $context->redirectToTranslation($lifecycle, '/kategorien/auffuehrung.php');
if ($resource !== null) {
	return $resource;
}

/* Lifecylce-Process */
$resource = $lifecycle->init([
	"id" => 32198,
	"version" => "1670935469267",
	"encoding" => "UTF-8",
	"locale" => "de_DE",
	"objectType" => "category",
	"url" => "/kategorien/auffuehrung.php",
	"created" => 1670935469,
	"changed" => 1670935469,
	"generated" => 1711458935,
	"ies" => [
		"id" => "100560100000032198-1015",
		"application" => "infosite6"
	],
	"name" => "Aufführung",
	"groupPath" => [
		[
			"id" => 1002,
			"groupType" => null
		],
		[
			"id" => 32198,
			"groupType" => null
		]
	],
	"contentSectionTypes" => [
	]
]);
if ($lifecycle->finish($resource)) { return $resource; }

if ($lifecycle->process("base", $resource)) { $resource->process("base", [
	"title" => "Aufführung"
]);}
if ($lifecycle->finish($resource)) { return $resource; }

if ($lifecycle->process("content", $resource)) { $resource->process("content", [
]); }
if ($lifecycle->finish($resource)) { return $resource; }

return $lifecycle->service($resource);
