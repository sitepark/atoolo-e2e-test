<?php

declare(strict_types=1);

namespace Atoolo\Form\Dto\UISchema;

/**
 * @codeCoverageIgnore
 */
class Annotation extends Element
{
    /**
     * @param array<string,mixed> $htmlLabel
     * @param array<string,mixed> $options
     */
    public function __construct(
        public array $htmlLabel = [],
        public readonly array $options = [],
    ) {
        parent::__construct(Type::ANNOTATION);
    }
}
