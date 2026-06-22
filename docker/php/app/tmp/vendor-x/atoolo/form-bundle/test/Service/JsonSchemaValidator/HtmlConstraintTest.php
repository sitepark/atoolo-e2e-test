<?php

declare(strict_types=1);

namespace Atoolo\Form\Test\Service\JsonSchemaValidator;

use Atoolo\Form\Service\JsonSchemaValidator\HtmlConstraint;
use Opis\JsonSchema\Errors\CustomError;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

#[CoversClass(HtmlConstraint::class)]
class HtmlConstraintTest extends TestCase
{
    private HtmlConstraint $constraint;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->constraint = new HtmlConstraint();
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
            'html',
            $this->constraint->getName(),
            'unexpected name',
        );
    }

    public function testCheckWithNonString(): void
    {
        $this->expectException(CustomError::class);
        $this->constraint->check(0, (object) []);
    }

    public static function dataToCheck(): array
    {
        return [
            [ 'abc', [], true ],
            [ 'abc', ['p', 'strong'], true ],
            [ '<p>abc <strong>test</strong></p>', ['p', 'strong'], true ],
            [ '<p>abc <b>test</b></p>', ['p', 'strong'], false ],
        ];
    }

    #[DataProvider('dataToCheck')]
    public function testCheck(
        string $value,
        array $allowedElements,
        bool $shouldValid,
    ): void {

        $schema = (object) ['allowedElements' => $allowedElements];

        if (!$shouldValid) {
            $this->expectException(CustomError::class);
        }

        $result = $this->constraint->check($value, $schema);
        if ($shouldValid) {
            $this->assertTrue($result, 'should be valid');
        }
    }
}
