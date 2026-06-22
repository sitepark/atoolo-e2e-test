<?php

declare(strict_types=1);

namespace Atoolo\Form\Test\Service;

use Atoolo\Form\Dto\FormDefinition;
use Atoolo\Form\Dto\FormSubmission;
use Atoolo\Form\Dto\UISchema\Layout;
use Atoolo\Form\Dto\UISchema\Type;
use Atoolo\Form\Processor\SubmitProcessor;
use Atoolo\Form\Service\SubmitHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(SubmitHandler::class)]
class SubmitHandlerTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testHandleWithMergedOptions(): void
    {
        $processor = $this->createMock(SubmitProcessor::class);
        $submitHandler = new SubmitHandler(
            [ 'test' => $processor ],
            [ 'test' => ['a' => 'b'] ],
        );

        $submit = $this->createSubmission([
            'test' => ['c' => 'd'],
        ]);

        $processor->expects($this->once())
            ->method('process')
            ->with($submit, ['a' => 'b', 'c' => 'd']);
        $submitHandler->handle($submit);
    }

    /**
     * @throws Exception
     */
    public function testHandleWithMissingProcessorKey(): void
    {
        $processor = $this->createMock(SubmitProcessor::class);
        $submitHandler = new SubmitHandler(
            [ 'test' => $processor, ],
            [ ],
        );

        $submit = $this->createSubmission([
            'x' => ['c' => 'd'],
        ]);

        $processor->expects($this->never())
            ->method('process');
        $submitHandler->handle($submit);
    }

    public function testConstructorWithTraversable(): void
    {

        $processor = $this->createMock(SubmitProcessor::class);
        $processors = (new \ArrayObject(['test' => $processor]))->getIterator();
        $submitHandler = new SubmitHandler(
            $processors,
            [ ],
        );

        $submit = $this->createSubmission([
            'test' => ['c' => 'd'],
        ]);

        $processor->expects($this->once())
            ->method('process')
            ->with($submit, ['c' => 'd']);
        $submitHandler->handle($submit);

    }

    private function createSubmission(array $processors): FormSubmission
    {
        $uischema = new Layout(Type::VERTICAL_LAYOUT);
        $formDefinition = new FormDefinition(
            schema: [],
            uischema: $uischema,
            data: [],
            buttons: [],
            messages: [],
            lang: 'en',
            component: 'test',
            processors: $processors,
        );
        return new FormSubmission(
            '127.0.0.1',
            $formDefinition,
            new stdClass(),
        );
    }
}
