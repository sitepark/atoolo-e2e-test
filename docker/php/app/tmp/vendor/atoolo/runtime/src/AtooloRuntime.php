<?php

declare(strict_types=1);

namespace Atoolo\Runtime;

use Atoolo\Runtime\Executor\RuntimeExecutor;

class AtooloRuntime
{
    /**
     * @var array<RuntimeExecutor>
     */
    private array $executor;

    /**
     * @param string $projectDir
     * @param RuntimeOptions $options
     */
    public function __construct(
        private readonly string $projectDir,
        private readonly array $options,
    ) {

        $executors = [];
        foreach ($options as $packageOptions) {
            $executors[] = array_map(
                static function (string $executorClass) {
                    return new $executorClass();
                },
                $packageOptions['executor'] ?? [],
            );
        }
        $this->executor = array_merge(...$executors);
    }

    public function run(): void
    {
        foreach ($this->executor as $executor) {
            $executor->execute($this->projectDir, $this->options);
        }
    }
}
