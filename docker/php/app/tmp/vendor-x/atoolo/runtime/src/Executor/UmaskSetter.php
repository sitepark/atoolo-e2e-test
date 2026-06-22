<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Executor;

use RuntimeException;

class UmaskSetter implements RuntimeExecutor
{
    /**
     * @var ?array{
     *   value: int,
     *   package: string
     * }
     */
    private ?array $alreadySet = null;

    public function __construct(
        private readonly Platform $platform = new Platform(),
    ) {}

    public function execute(string $projectDir, array $options): void
    {
        $umask = false;
        foreach ($options as $package => $packageOptions) {
            if (!isset($packageOptions['umask'])) {
                continue;
            }
            $value = $this->validate($package, $packageOptions['umask']);
            if ($value !== false) {
                $umask = $value;
            }
        }
        if ($umask !== false) {
            $this->platform->umask($umask);
        }
    }

    private function validate(string $package, mixed $value): false|int
    {
        if (!is_numeric($value)) {
            throw new RuntimeException(
                "[atoolo.runtime.umask]: '
                    . 'umask must be an integer: "
                . $value,
            );
        }

        $umask = (int) $value;

        if ($this->alreadySet !== null) {
            $existsValue = $this->alreadySet['value'];
            if ($existsValue === $umask) {
                return false;
            }
            $package  = $this->alreadySet['package'];
            throw new RuntimeException(
                "[atoolo.runtime.umask]: '
                    . 'umask is already set to $existsValue '
                    . ' for package $package",
            );
        }

        $this->alreadySet = [
            'value' => $umask,
            'package' => $package,
        ];

        return $umask;
    }
}
