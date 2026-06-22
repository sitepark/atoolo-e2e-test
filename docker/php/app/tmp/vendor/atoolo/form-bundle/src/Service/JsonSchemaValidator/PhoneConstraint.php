<?php

declare(strict_types=1);

namespace Atoolo\Form\Service\JsonSchemaValidator;

use stdClass;

class PhoneConstraint implements FormatConstraint
{
    public function getType(): string
    {
        return 'string';
    }

    public function getName(): string
    {
        return 'phone';
    }

    public function check(mixed $value, stdClass $schema): bool
    {
        // TODO: It is not yet clear which format is expected here.
        return true;
    }
}
