<?php

declare(strict_types=1);

namespace Atoolo\Form\Service\JsonSchemaValidator;

use stdClass;

interface FormatConstraint extends Constraint
{
    /**
     * Returns the JSON-Schema type that this constraint applies to.
     */
    public function getType(): string;

    /**
     * Returns the format name to which this constraint applies.
     */
    public function getName(): string;

    /**
     * Validates the given value against the format constraint.
     *
     * @param mixed $value The value to validate.
     * @param stdClass $schema The schema that defines the format constraint.
     * @return bool Whether the value is valid.
     */
    public function check(mixed $value, stdClass $schema): bool;
}
