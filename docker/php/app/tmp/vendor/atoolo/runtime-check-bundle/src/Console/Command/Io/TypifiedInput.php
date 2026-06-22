<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Check\Console\Command\Io;

use InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;

/**
* This class can be used to obtain type-safe return values.
 * Necessary to pass the PHPStan checks.
 */
class TypifiedInput
{
    public function __construct(private readonly InputInterface $input) {}

    public function getStringOption(string $name): ?string
    {
        $value = $this->input->getOption($name);
        if ($value === null) {
            return null;
        }
        if (!is_string($value)) {
            throw new InvalidArgumentException(
                'option ' . $name . ' must be a string: ' . $value,
            );
        }
        return $value;
    }

    /**
     * @return array<string>
     */
    public function getArrayOption(string $name): array
    {
        $value = $this->input->getOption($name);
        if ($value === null) {
            return [];
        }
        if (!is_array($value)) {
            throw new InvalidArgumentException(
                'option ' . $name . ' must be a array: ' . $value,
            );
        }
        return $value;
    }


    public function getBoolOption(string $name): bool
    {
        $value = $this->input->getOption($name);
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }
}
