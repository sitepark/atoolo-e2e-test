<?php

declare(strict_types=1);

namespace Atoolo\Form\Test\Service\JsonSchemaValidator;

use Atoolo\Form\Dto\FormData\UploadFile;
use Atoolo\Form\Service\DataUrlParser;
use Atoolo\Form\Service\JsonSchemaValidator\DataUrlConstraint;
use Opis\JsonSchema\Errors\CustomError;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

#[CoversClass(DataUrlConstraint::class)]
class DataUrlConstraintTest extends TestCase
{
    private DataUrlParser $dataUrlParser;
    private DataUrlConstraint $constraint;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->dataUrlParser = $this->createStub(DataUrlParser::class);
        $this->constraint = new DataUrlConstraint($this->dataUrlParser);
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
            'data-url',
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
        $pngData = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAgAAAAIAQMAAAD+wSzIAAAABlBMVEX///+/v7+jQ3Y5AAAADklEQVQI12P4AIX8EAgALgAD/aNpbtEAAAAASUVORK5CYII');
        return [
            [ ['size' => 5], ['maxFileSize' => 10], true ],
            [ ['size' => 10], ['maxFileSize' => 10], true ],
            [ ['size' => 20], ['maxFileSize' => 10], false ],
            [ ['size' => 5], ['minFileSize' => 10], false ],
            [ ['size' => 10], ['minFileSize' => 10], true ],
            [ ['size' => 20], ['minFileSize' => 10], true ],
            [ ['data' => $pngData], ['acceptedContentTypes' => ['image/png']], true ],
            [ ['data' => $pngData], ['acceptedContentTypes' => ['image/*']], true ],
            [ ['data' => $pngData], ['acceptedContentTypes' => ['image/jpg']], false ],
            [ ['data' => $pngData], ['acceptedContentTypes' => ['text/plain']], false ],
            [ ['data' => $pngData], ['acceptedContentTypes' => ['text/plain', 'image/png']], true ],
            [ ['filename' => 'test.txt'], ['acceptedFileNames' => ['test.txt']], true ],
            [ ['filename' => 'test.txt'], ['acceptedFileNames' => ['test.*']], true ],
            [ ['filename' => 'test.txt'], ['acceptedFileNames' => ['*.txt']], true ],
            [ ['filename' => 'test.txt'], ['acceptedFileNames' => ['*.png']], false ],
            [ ['filename' => 'test.txt'], ['acceptedFileNames' => ['*.txt', 'image.png']], true ],
        ];
    }

    #[DataProvider('dataToCheck')]
    public function testCheck(
        array $uploadFileFields,
        array $schema,
        bool $shouldValid,
    ): void {

        $uploadFile = new UploadFile(
            $uploadFileFields['filename'] ?? '',
            $uploadFileFields['mimeType'] ?? '',
            $uploadFileFields['data'] ?? '',
            $uploadFileFields['size'] ?? 0,
        );

        $this->dataUrlParser->method('parse')->willReturn($uploadFile);

        if (!$shouldValid) {
            $this->expectException(CustomError::class);
        }

        $result = $this->constraint->check('data-url', (object) $schema);
        if ($shouldValid) {
            $this->assertTrue($result, 'should be valid');
        }
    }
}
