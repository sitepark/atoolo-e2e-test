<?php

declare(strict_types=1);

namespace Atoolo\Form\Service\JsonSchemaValidator;

use Opis\JsonSchema\Errors\CustomError;
use stdClass;

class HtmlConstraint implements FormatConstraint
{
    public function getType(): string
    {
        return 'string';
    }

    public function getName(): string
    {
        return 'html';
    }

    /**
     * @throws CustomError
     */
    public function check(mixed $value, stdClass $schema): bool
    {
        if (!is_string($value)) {
            throw new CustomError('Value is not a string');
        }

        if (isset($schema->allowedElements)) {
            $stripped = strip_tags($value, $schema->allowedElements);
            if ($stripped !== $value) {
                throw new CustomError(
                    'HTML contains disallowed elements (allowedElemnets: ' . implode(',', $schema->allowedElements) . ')',
                    ['allowedElements' => $schema->allowedElements],
                );
            }
        }
        return true;
    }

}
