<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Test;

use Atoolo\Runtime\Executor\RuntimeExecutor;

class AtooloRuntimeTestExecutor implements RuntimeExecutor
{
    public static bool $executed = false;
    public function execute(string $projectDir, array $options): void
    {
        self::$executed = true;
    }
}
