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
	"id" => 1118,
	"version" => "1694758793440",
	"encoding" => "UTF-8",
	"locale" => "de_DE",
	"objectType" => "home",
	"url" => "/index.php",
	"created" => 1553175648,
	"changed" => 1694758793,
	"generated" => 1714718857,
	"ies" => [
		"id" => "100560100000001118-1015",
		"application" => "infosite6"
	],
	"name" => "Startseite",
	"anchor" => "internetwebsite.home",
	"home" => true,
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
			"id" => 1118,
			"groupType" => null
		]
	],
	"contentSectionTypes" => [
	]
]);
if ($lifecycle->finish($resource)) { return $resource; }

if ($lifecycle->process("base", $resource)) { $resource->process("base", [
	"date" => 1571988240,
	"siteTitle" => "Webauftritt",
	"trees" => [
		"navigation" => [
			"children" => [
			]
		]
	],
	"title" => "Startseite",
	"teaser" => [
		"date" => 1571988240,
		"headline" => "Startseite",
		"text" => "Der neue Webauftritt mit aktuellen Informationen."
	],
	"kicker" => "Webauftritt"
]);}
if ($lifecycle->finish($resource)) { return $resource; }

if ($lifecycle->process("metadata", $resource)) { $resource->process("metadata", [
	"description" => "Der neue Webauftritt mit aktuellen Informationen."
]); }
if ($lifecycle->finish($resource)) { return $resource; }

if ($lifecycle->process("searchindexdata", $resource)) { $resource->process("searchindexdata", [
	"content" => ""
]); }
if ($lifecycle->finish($resource)) { return $resource; }

if ($lifecycle->process("content", $resource)) { $resource->process("content", [
]); }
if ($lifecycle->finish($resource)) { return $resource; }

return $lifecycle->service($resource);

