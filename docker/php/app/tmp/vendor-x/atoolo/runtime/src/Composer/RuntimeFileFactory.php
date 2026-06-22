<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Composer;

use Composer\Composer;

class RuntimeFileFactory
{
    public function create(
        Composer $composer,
        string $projectDir,
    ): RuntimeFile {
        return new RuntimeFile($composer, $projectDir);
    }
}
