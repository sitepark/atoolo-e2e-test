<?php

return new \Atoolo\Resource\Resource(
    '/index.php',
    'root',
    'root',
    '',
    \Atoolo\Resource\ResourceLanguage::of('de_DE'),
    new \Atoolo\Resource\DataBag([
        'home' => true,
    ]),
);
