<?php

declare(strict_types=1);

namespace Atoolo\Form\Service\JsonSchemaValidator\Extended;

use Opis\JsonSchema\Parsers\Drafts\Draft202012;
use Opis\JsonSchema\Parsers\Keywords\FormatKeywordParser;

/**
 * An extension so that the format validator can also be passed the schema.
 * For details see https://github.com/opis/json-schema/issues/142
 * @codeCoverageIgnore
 */
class Draft202012Extended extends Draft202012
{
    public function version(): string
    {
        return '2020-12-extended';
    }

    protected function getKeywordParsers(): array
    {
        return array_map(function ($parser) {
            if ($parser instanceof FormatKeywordParser) {
                // replace the FormatKeywordParser with our extended version
                return new FormatKeywordParserExtended('format');
            }
            return $parser;
        }, parent::getKeywordParsers());
    }
}
