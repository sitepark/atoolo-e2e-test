<?php

declare(strict_types=1);

namespace Atoolo\Form\Test\Service;

use Atoolo\Form\Dto\FormDefinition;
use Atoolo\Form\Dto\UISchema\Control;
use Atoolo\Form\Dto\UISchema\Layout;
use Atoolo\Form\Dto\UISchema\Type;
use Atoolo\Form\Service\FormReader;
use Atoolo\Form\Service\FromReaderHandler;
use Atoolo\Form\Service\JsonSchemaValidator\Extended\Draft202012Extended;
use Atoolo\Resource\ResourceLocation;
use JsonSchema\Validator;
use Opis\JsonSchema\Errors\ErrorFormatter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

#[CoversClass(FormReader::class)]
class FormReaderTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testWithMissingSchemaField(): void
    {
        $uischema = new Layout(
            Type::GROUP,
            [
                new Control('#/properties/field-2'),
            ],
        );
        $schema = [
            'type' => 'object',
            'properties' => [
                'field-1' => [
                    'type' => 'string',
                ],
            ],
        ];

        $formDefinition = $this->createDefinition($schema, $uischema);
        $data = [
            'field-1' => 'value',
        ];

        $handler = $this->createMock(FromReaderHandler::class);
        $handler->expects($this->once())
            ->method('startLayout')
            ->with($uischema);
        $handler->expects($this->never())
            ->method('control');
        $handler->expects($this->once())
            ->method('endLayout')
            ->with($uischema);

        $reader = new FormReader($formDefinition, $data, $handler);
        $reader->read();
    }

    /**
     * @throws Exception
     */
    public function testLoad(): void
    {
        $control = new Control('#/properties/field-1');
        $uischema = new Layout(
            Type::GROUP,
            [ $control ],
        );
        $schema = [
            'type' => 'object',
            'properties' => [
                'field-1' => [
                    'type' => 'string',
                ],
            ],
        ];

        $formDefinition = $this->createDefinition($schema, $uischema);

        $data = [
            'field-1' => 'value',
        ];

        $handler = $this->createMock(FromReaderHandler::class);
        $handler->expects($this->once())
            ->method('startLayout')
            ->with($uischema);
        $handler->expects($this->once())
            ->method('control')
            ->with($control, ['type' => 'string'], 'field-1', 'value');
        $handler->expects($this->once())
            ->method('endLayout')
            ->with($uischema);

        $reader = new FormReader($formDefinition, $data, $handler);
        $reader->read();
    }

    private function createDefinition(
        array $schema,
        Layout $uischema,
    ): FormDefinition {
        return new FormDefinition(
            schema: $schema,
            uischema: $uischema,
            data: null,
            buttons: [],
            messages: [],
            lang: 'en',
            component: 'form-1',
            processors: [],
        );
    }
}
