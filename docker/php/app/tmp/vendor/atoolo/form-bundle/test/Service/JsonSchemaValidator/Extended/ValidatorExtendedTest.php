<?php

declare(strict_types=1);

namespace Atoolo\Form\Test\Service\JsonSchemaValidator\Extended;

use Atoolo\Form\Service\JsonSchemaValidator\Extended\ValidatorExtended;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use stdClass;

#[CoversClass(ValidatorExtended::class)]
class ValidatorExtendedTest extends TestCase
{
    public function testExtendedValidator(): void
    {

        $validator = new ValidatorExtended();

        $expectedSchema = new stdClass();
        $expectedSchema->type = 'string';
        $expectedSchema->format = 'test';

        $formatResolver = $validator->parser()->getFormatResolver();
        $formatResolver->registerCallable('string', 'test', function ($data, $schema) use ($expectedSchema) {
            $this->assertEquals('abc', $data, "unexpected data");
            $this->assertEquals($expectedSchema, $schema, "unexpected schema");
            return true;
        });

        $schema = json_encode([
            "type" => "object",
            "properties" => [
                "test" => [
                    "type" => "string",
                    "format" => "test",
                ],
            ],
        ], JSON_THROW_ON_ERROR);
        $json = json_encode([
            'test' => 'abc',
        ], JSON_THROW_ON_ERROR);

        $validator->validate(
            json_decode($json, false, 512, JSON_THROW_ON_ERROR),
            $schema,
        );
    }
}
