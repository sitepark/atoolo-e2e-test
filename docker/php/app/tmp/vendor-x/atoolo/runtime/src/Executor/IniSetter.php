<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Executor;

use RuntimeException;

class IniSetter implements RuntimeExecutor
{
    /**
     * @var array<string,array{
     *     value: bool|float|int|string,
     *     package: string
     * }>
     */
    private $alreadySet = [];

    public function __construct(
        private readonly Platform $platform = new Platform(),
    ) {}

    /**
     * @param RuntimeOptions $options
     * @throws RuntimeException if multiple packages define the same ini option
     */
    public function execute(string $projectDir, array $options): void
    {
        /**
         * @var array<string,array{
         *     value: bool|float|int|string,
         *     package: string
         * }> $settings
         */
        $settings = [];
        foreach ($options as $package => $packageOptions) {
            foreach ($packageOptions['ini']['set'] ?? [] as $key => $value) {
                $value = $this->validate($package, $key, $value);
                if ($value !== null) {
                    $settings[$key] = [
                        'value' => $value,
                        'package' => $package,
                    ];
                }
            }
        }

        foreach ($settings as $key => $setting) {
            $value = $setting['value'];
            if ($this->platform->setIni($key, $value) === false) {
                $package = $setting['package'];
                throw new RuntimeException(
                    "[atoolo.runtime.init.set]: "
                    . "Failed to set $key to $value for, package: $package",
                );
            }
        }
    }

    /**
     * @return bool|float|int|string|null returns the typed value
     * @throws RuntimeException
     *  if the ini option non-scalar or has already been set by this instance
     */
    private function validate(
        string $package,
        string $key,
        mixed $value,
    ): bool|float|int|string|null {

        if ($value === null) {
            return null;
        }

        if (is_scalar($value) === false) {
            throw new RuntimeException(
                "[atoolo.runtime.init.set]: "
                . "Value for $key in package $package must be scalar",
            );
        }

        if (isset($this->alreadySet[$key])) {
            $existsValue = $this->alreadySet[$key]['value'];
            if ($existsValue === $value) {
                return null;
            }
            $package  = $this->alreadySet[$key]['package'];
            throw new RuntimeException(
                "[atoolo.runtime.init.set]: "
                . "$key is already set to '$existsValue', package: $package",
            );
        }

        $this->alreadySet[$key] = [
            'value' => $value,
            'package' => $package,
        ];

        return $value;
    }
}
