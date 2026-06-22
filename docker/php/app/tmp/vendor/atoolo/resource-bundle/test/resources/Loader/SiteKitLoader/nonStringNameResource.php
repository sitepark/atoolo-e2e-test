<?php

/* Bootstrap */
if (!isset($context)) {
    $context = include(
        __DIR__ .
        '/./WEB-IES/sitekit-module/php/bootstrapper.php'
    );
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
    "name" => 123,
    "id" => 1118,
    "objectType" => "home",
]);

return $lifecycle->service($resource);
