<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Executor;

interface RuntimeExecutor
{
    /**
     * @param RuntimeOptions $options
     */
    public function execute(string $projectDir, array $options): void;
}
