<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Executor;

use RuntimeException;
use Symfony\Component\Dotenv\Dotenv;

class EnvSetter implements RuntimeExecutor
{
    /**
     * @var array<string,array{
     *     value: bool|float|int|string,
     *     package: string
     * }>
     */
    private $alreadySet = [];

    private Dotenv $dotenv;

    public function __construct(
        private readonly Platform $platform = new Platform(),
    ) {
        $this->dotenv = new Dotenv();
    }

    /**
     * @param RuntimeOptions $options
     * @throws RuntimeException if multiple packages define the same ini option
     */
    public function execute(string $projectDir, array $options): void
    {
        /**
         * @var array<string,array{
         *     value: string,
         *     package: string
         * }> $settings
         */
        $settings = [];
        foreach ($options as $package => $packageOptions) {

            $file = $packageOptions['env']['file'] ?? '';

            $env = array_merge(
                $this->loadEnvironmentFile($package, $file),
                $packageOptions['env']['set'] ?? [],
            );


            foreach ($env as $key => $value) {
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
            if ($this->platform->putEnv($key, $value) === false) {
                $package = $setting['package'];
                throw new RuntimeException(
                    "[atoolo.runtime.env.set]: "
                    . "Failed to set $key to $value for, package: $package",
                );
            }
        }
    }

    /**
     * @throws RuntimeException
     *  if the ini option non-scalar or has already been set by this instance
     */
    private function validate(
        string $package,
        string $key,
        mixed $value,
    ): ?string {

        if ($value === null) {
            return null;
        }

        if (is_string($value) === false) {
            throw new RuntimeException(
                "[atoolo.runtime.init.set]: "
                . "Value for $key in package $package must be string",
            );
        }

        if (isset($this->alreadySet[$key])) {
            $existsValue = $this->alreadySet[$key]['value'];
            if ($existsValue === $value) {
                return null;
            }
            $package  = $this->alreadySet[$key]['package'];
            throw new RuntimeException(
                "[atoolo.runtime.env.set]: "
                . "$key is already set to '$existsValue', package: $package",
            );
        }

        $this->alreadySet[$key] = [
            'value' => $value,
            'package' => $package,
        ];

        return $value;
    }

    /**
     * @return array<string,string>
     */
    private function loadEnvironmentFile(string $package, string $file): array
    {
        if (empty($file)) {
            return [];
        }
        if (!file_exists($file)) {
            return [];
        }

        $content = $this->platform->readFile($file);
        return $this->dotenv->parse($content);
    }
}
