<?php

declare(strict_types=1);

namespace Atoolo\Form\Test\Service;

use Atoolo\Form\Service\JsonSchemaValidator;
use Atoolo\Form\Service\JsonSchemaValidator\Constraint;
use Atoolo\Form\Service\JsonSchemaValidator\FormatConstraint;
use Atoolo\Form\Service\Platform;
use LogicException;
use Opis\JsonSchema\Errors\ValidationError;
use Opis\JsonSchema\Info\SchemaInfo;
use Opis\JsonSchema\Parsers\SchemaParser;
use Opis\JsonSchema\Resolvers\FormatResolver;
use Opis\JsonSchema\Schema;
use Opis\JsonSchema\ValidationResult;
use Opis\JsonSchema\Validator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use InvalidArgumentException;
use stdClass;
use Symfony\Component\Validator\Exception\ValidationFailedException;

#[CoversClass(JsonSchemaValidator::class)]
class JsonSchemaValidatorTest extends TestCase
{
    private Validator $validator;

    private JsonSchemaValidator $jsonSchemaValidator;

    private FormatConstraint $formatConstraint;

    private FormatResolver&MockObject $formatResolver;
    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->validator = $this->createStub(Validator::class);
        $this->formatResolver = $this->createMock(FormatResolver::class);
        $schemaParser = $this->createStub(SchemaParser::class);
        $schemaParser->method('getFormatResolver')->willReturn($this->formatResolver);
        $this->validator->method('parser')->willReturn($schemaParser);
        $this->formatConstraint = $this->createStub(FormatConstraint::class);
        $this->jsonSchemaValidator = new JsonSchemaValidator(
            $this->validator,
            [$this->formatConstraint],
            new Platform(),
        );
    }

    /**
     * @throws Exception
     */
    public function testWithInvalidConstrains(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new JsonSchemaValidator(
            $this->validator,
            [$this->createStub(Constraint::class)],
            new Platform(),
        );
    }

    /**
     * @throws Exception
     */
    public function testWithMissingFormatResolver(): void
    {
        $this->expectException(LogicException::class);

        $validator = $this->createStub(Validator::class);

        new JsonSchemaValidator(
            $validator,
            [$this->createStub(FormatConstraint::class)],
            new Platform(),
        );
    }

    /**
     * @throws Exception
     */
    public function testRegisterFormatConstraint(): void
    {
        $formatConstraint = $this->createStub(FormatConstraint::class);
        $formatConstraint->method('getType')->willReturn('string');
        $formatConstraint->method('getName')->willReturn('test');

        $this->formatResolver->expects($this->once())
            ->method('registerCallable')
            ->with(
                'string',
                'test',
                $this->callback(
                    static function ($callback) {
                        return is_callable($callback);
                    },
                ),
            );

        new JsonSchemaValidator(
            $this->validator,
            [$formatConstraint],
            new Platform(),
        );
    }

    /**
     * @throws Exception
     */
    public function testFormatConstraintCheckCallback(): void
    {
        $formatConstraint = $this->createStub(FormatConstraint::class);
        $formatConstraint->method('getType')->willReturn('string');
        $formatConstraint->method('getName')->willReturn('test');
        $formatConstraint->method('check')->willReturn(true);

        $validator = $this->createStub(Validator::class);
        $formatResolver = new FormatResolver();
        $schemaParser = $this->createStub(SchemaParser::class);
        $schemaParser->method('getFormatResolver')->willReturn($formatResolver);
        $validator->method('parser')->willReturn($schemaParser);

        new JsonSchemaValidator(
            $validator,
            [$formatConstraint],
            new Platform(),
        );

        $callback = $formatResolver->resolve('test', 'string');
        $this->assertTrue($callback('data', new stdClass()), 'Callback should return true');
    }


    /**
     * @throws Exception
     * @throws \JsonException
     */
    public function testValidate(): void
    {
        $schema = [
            'type' => 'object',
            'properties' => [
                'text' => [
                    'type' => 'string',
                ],
            ],
            'required' => ['text'],
        ];

        $data = new stdClass();

        $result = $this->createValidationResult(
            path: ['test'],
            message: 'Field missing',
            constraint: 'require',
            args: ['missing' => ['text']],
        );
        $this->validator->method('validate')->willReturn($result);

        try {
            $this->jsonSchemaValidator->validate($schema, $data);
        } catch (ValidationFailedException $e) {
            $violation = $e->getViolations()->get(0);
            $this->assertEquals('test/text', $violation->getPropertyPath());
        }
    }

    /**
     * @throws Exception
     */
    private function createValidationResult(
        array $path,
        string $message,
        string $constraint,
        array $args,
    ): ValidationResult {
        $schemaInfo = $this->createStub(SchemaInfo::class);
        $schemaInfo->method('path')->willReturn($path);
        $schema = $this->createStub(Schema::class);
        $schema->method('info')->willReturn($schemaInfo);

        $validationError = $this->createStub(ValidationError::class);
        $validationError->method('keyword')->willReturn($constraint);
        $validationError->method('schema')->willReturn($schema);
        $validationError->method('args')->willReturn($args);
        $validationError->method('message')->willReturn($message);
        return new ValidationResult($validationError);
    }
}
