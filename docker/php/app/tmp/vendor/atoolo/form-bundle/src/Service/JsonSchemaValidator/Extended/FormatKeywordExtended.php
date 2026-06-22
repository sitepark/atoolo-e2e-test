<?php

declare(strict_types=1);

namespace Atoolo\Form\Service\JsonSchemaValidator\Extended;

use Opis\JsonSchema\Errors\CustomError;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Format;
use Opis\JsonSchema\Keyword;
use Opis\JsonSchema\Keywords\ErrorTrait;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\ValidationContext;

/**
 * Copied and modified from https://github.com/opis/json-schema/blob/master/src/Keywords/FormatKeyword.php
 * see: "modified line"
 *
 * @codeCoverageIgnore
 */
class FormatKeywordExtended implements Keyword
{
    use ErrorTrait;

    protected ?string $name;

    /** @var callable[]|Format[] */
    protected ?array $types;

    /**
     * @param string $name
     * @param callable[]|Format[] $types
     */
    public function __construct(string $name, array $types)
    {
        $this->name = $name;
        $this->types = $types;
    }

    /**
     * @inheritDoc
     */
    public function validate(ValidationContext $context, Schema $schema): ?ValidationError
    {
        $type = $context->currentDataType();

        if (!isset($this->types[$type])) {
            return null;
        }

        $format = $this->types[$type];

        try {
            if ($format instanceof Format) {
                $ok = $format->validate($context->currentData());
            } else {
                // modified line
                $ok = $format($context->currentData(), $schema->info()->data());
            }
        } catch (CustomError $error) {
            return $this->error($schema, $context, 'format', $error->getMessage(), $error->getArgs() + [
                'format' => $this->name,
                'type' => $type,
            ]);
        }

        if ($ok) {
            return null;
        }

        return $this->error($schema, $context, 'format', "The data must match the '{format}' format", [
            'format' => $this->name,
            'type' => $type,
        ]);
    }
}
