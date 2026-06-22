<?php

declare(strict_types=1);

namespace Atoolo\Form\Dto\UISchema;

/**
 * @codeCoverageIgnore
 */
class Layout extends Element
{
    /** @var array<Element> */
    public array $elements;

    /**
     * @param Type $type
     * @param array<Element> $elements
     * @param string|bool|null $label
     * @param array<string,mixed> $options
     */
    public function __construct(
        Type $type,
        array $elements = [],
        public string|bool|null $label = null,
        public array $options = [],
    ) {
        parent::__construct($type);
        $this->elements = $elements;
    }
}
