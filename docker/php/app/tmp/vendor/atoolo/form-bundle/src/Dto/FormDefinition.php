<?php

declare(strict_types=1);

namespace Atoolo\Form\Dto;

use Atoolo\Form\Dto\UISchema\Element;
use Symfony\Component\Serializer\Attribute\Ignore;

/**
 * @codeCoverageIgnore
 */
class FormDefinition
{
    /**
     * @param JsonSchema $schema
     * @param array<string, mixed> $data Default values of form fields that should be prefilled
     * @param array{
     *     submit?: string,
     * } $buttons
     * @param array<string, array{
     *     headline: string,
     *     text: string,
     * }>|null $messages
     * @param array<string, array<string,mixed>>|null $processors
     */
    public function __construct(
        public readonly array $schema,
        public readonly Element $uischema,
        public readonly ?array $data,
        public readonly array $buttons,
        public readonly ?array $messages,
        public readonly string $lang,
        public readonly string $component,
        #[Ignore]
        public readonly ?array $processors,
    ) {}
}
