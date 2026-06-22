<?php

declare(strict_types=1);

namespace Atoolo\Form\Service;

use Atoolo\Form\Dto\FormDefinition;
use Atoolo\Form\Dto\UISchema\Control;
use Atoolo\Form\Dto\UISchema\Element;
use Atoolo\Form\Dto\UISchema\Layout;

class FormReader
{
    /**
     * @param array<string,mixed> $data
     */
    public function __construct(
        public readonly FormDefinition $formDefinition,
        public readonly array $data,
        public readonly FromReaderHandler $handler,
    ) {}

    public function read(): void
    {
        $this->readElement($this->formDefinition->uischema);
    }

    private function readElement(Element $element): void
    {
        if ($element instanceof Control) {
            $this->readControl($element);
        } elseif ($element instanceof Layout) {
            $this->readLayout($element);
        }
    }

    private function readControl(Control $control): void
    {
        /**
         * @var JsonSchema|null $schema
         * @var string $name
         * @var array<string,mixed> $data
         */
        [$schema, $name, $data] = $this->getValue($control->scope);
        if ($schema === null) {
            return;
        }
        $this->handler->control($control, $schema, $name, $data);
    }

    private function readLayout(Layout $layout): void
    {
        $this->handler->startLayout($layout);
        foreach ($layout->elements as $element) {
            $this->readElement($element);
        }
        $this->handler->endLayout($layout);
    }

    /**
     * @return array<JsonSchema|string|array<string,mixed>|null>
     */
    private function getValue(string $scope): array
    {
        $keys = explode('/', ltrim($scope, '#'));
        $name = end($keys);

        /** @var array<string,mixed> $data */
        $data = $this->data;

        /** @var JsonSchema $schema */
        $schema = $this->formDefinition->schema;

        foreach ($keys as $key) {

            if (empty($key)) {
                continue;
            }

            if ($schema !== null && isset($schema[$key])) {
                $schema = $schema[$key];
            } else {
                $schema = null;
            }

            if ($key === 'properties') {
                continue;
            }
            if (isset($data[$key])) {
                /** @var array<string,mixed> $data */
                $data = $data[$key];
            } else {
                $data = null;
                break;
            }
        }

        return [$schema, $name, $data];
    }
}
