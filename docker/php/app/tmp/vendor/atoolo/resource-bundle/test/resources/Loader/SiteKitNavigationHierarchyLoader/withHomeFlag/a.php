<?php

return new \Atoolo\Resource\Resource(
    '/a.php',
    'a',
    'a',
    '',
    \Atoolo\Resource\ResourceLanguage::of('de_DE'),
    new \Atoolo\Resource\DataBag([
        'home' => true,
    ]),
);
