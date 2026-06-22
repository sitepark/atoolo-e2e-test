<?php

declare(strict_types=1);

namespace Atoolo\Form\Test\Processor;

use Atoolo\Form\Dto\FormDefinition;
use Atoolo\Form\Dto\FormSubmission;
use Atoolo\Form\Dto\UISchema\Element;
use Atoolo\Form\Dto\UISchema\Layout;
use Atoolo\Form\Dto\UISchema\Type;
use Atoolo\Form\Processor\JsonSchemaValidator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(JsonSchemaValidator::class)]
class JsonSchemaValidatorTest extends TestCase
{
    private \Atoolo\Form\Service\JsonSchemaValidator&MockObject $service;
    private JsonSchemaValidator $processor;

    private FormSubmission $submission;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->service = $this->createMock(\Atoolo\Form\Service\JsonSchemaValidator::class);
        $this->processor = new JsonSchemaValidator($this->service);

        $formDefinition = new FormDefinition(
            schema: [],
            uischema: new Layout(Type::VERTICAL_LAYOUT),
            data: [],
            buttons: [],
            messages: [],
            lang: 'en',
            component: 'test',
            processors: [],
        );
        $this->submission = new FormSubmission(
            '127.0.0.1',
            $formDefinition,
            new stdClass(),
        );

    }

    public function testProcess(): void
    {
        $this->service->expects($this->once())
            ->method('validate');
        $this->processor->process($this->submission, []);
    }
}
