<?php

declare(strict_types=1);

namespace Atoolo\Form\Service\JsonSchemaValidator\Extended;

use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Keyword;
use Opis\JsonSchema\Keywords\FormatDataKeyword;
use Opis\JsonSchema\Parsers\DataKeywordTrait;
use Opis\JsonSchema\Parsers\Keywords\FormatKeywordParser;
use Opis\JsonSchema\Parsers\ResolverTrait;
use Opis\JsonSchema\Parsers\SchemaParser;

/**
 * Copied and modified from https://github.com/opis/json-schema/blob/master/src/Parsers/Keywords/FormatKeywordParser.php
 * see: "modified line"
 * @codeCoverageIgnore
 */
class FormatKeywordParserExtended extends FormatKeywordParser
{
    use ResolverTrait;
    use DataKeywordTrait;

    /**
     * @inheritDoc
     */
    public function type(): string
    {
        return self::TYPE_BEFORE;
    }

    /**
     * @inheritDoc
     */
    public function parse(SchemaInfo $info, SchemaParser $parser, object $shared): ?Keyword
    {
        $schema = $info->data();

        $resolver = $parser->getFormatResolver();

        if (!$resolver || !$parser->option('allowFormats') || !$this->keywordExists($schema)) {
            return null;
        }

        $value = $this->keywordValue($schema);

        if ($this->isDataKeywordAllowed($parser, $this->keyword)) {
            if ($pointer = $this->getDataKeywordPointer($value)) {
                return new FormatDataKeyword($pointer, $resolver);
            }
        }

        if (!is_string($value)) {
            throw $this->keywordException("{keyword} must be a string", $info);
        }

        $list = $resolver->resolveAll($value);

        if (!$list) {
            return null;
        }

        // modified line
        return new FormatKeywordExtended($value, $this->resolveSubTypes($list));
    }
}
