<?php

declare(strict_types=1);

namespace Atoolo\Form\Test\Service\JsonSchemaValidator;

use Atoolo\Form\Service\JsonSchemaValidator\PhoneConstraint;
use Opis\JsonSchema\Errors\CustomError;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

#[CoversClass(PhoneConstraint::class)]
class PhoneConstraintTest extends TestCase
{
    private PhoneConstraint $constraint;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->constraint = new PhoneConstraint();
    }

    public function testGetType(): void
    {
        $this->assertEquals(
            'string',
            $this->constraint->getType(),
            'unexpected type',
        );
    }

    public function testGetName(): void
    {
        $this->assertEquals(
            'phone',
            $this->constraint->getName(),
            'unexpected name',
        );
    }

    public static function dataToCheck(): array
    {
        return [
            [ 'abc', true ],
        ];
    }

    #[DataProvider('dataToCheck')]
    public function testCheck(
        string $value,
        bool $shouldValid,
    ): void {

        $schema = (object) [];

        if (!$shouldValid) {
            $this->expectException(CustomError::class);
        }

        $result = $this->constraint->check($value, $schema);
        if ($shouldValid) {
            $this->assertTrue($result, 'should be valid');
        }
    }
}
