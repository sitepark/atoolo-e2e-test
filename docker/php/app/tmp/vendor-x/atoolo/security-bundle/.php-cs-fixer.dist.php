<?php

declare(strict_types=1);

$finder = (new \PhpCsFixer\Finder())
    ->in(__DIR__ . '/src')
    ->in(__DIR__ . '/test')
    ->files()
    ->notName('broken.users.php')
    ->notName('broken.access.php');

return (new \PhpCsFixer\Config())
    ->setCacheFile('var/cache/php-cs-fixer')
    ->setFinder($finder)
    ->setRules(['@PER-CS' => true]);
