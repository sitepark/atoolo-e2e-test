<?php

declare(strict_types=1);

namespace Atoolo\Form\Service\JsonSchemaValidator;

use Atoolo\Form\Service\DataUrlParser;
use finfo;
use Opis\JsonSchema\Errors\CustomError;
use stdClass;

class DataUrlConstraint implements FormatConstraint
{
    public function __construct(
        private readonly DataUrlParser $dataUrlParser,
    ) {}

    public function getType(): string
    {
        return 'string';
    }

    public function getName(): string
    {
        return 'data-url';
    }

    /**
     * @throws CustomError
     */
    public function check(mixed $value, stdClass $schema): bool
    {
        if (!is_string($value)) {
            throw new CustomError('Value is not a string');
        }

        $uploadFile = $this->dataUrlParser->parse($value);

        if (isset($schema->maxFileSize) && $uploadFile->size > $schema->maxFileSize) {
            throw new CustomError(
                'File size (' . $uploadFile->size . ' bytes) exceeds maximum allowed size (' . $schema->maxFileSize . ' bytes)',
                ['maxFileSize' => $schema->maxFileSize],
            );
        }
        if (isset($schema->minFileSize) && $uploadFile->size < $schema->minFileSize) {
            throw new CustomError(
                'File size (' . $uploadFile->size . ' bytes) is less than minimum allowed size (' . $schema->minFileSize . ' bytes)',
                ['minFileSize' => $schema->minFileSize],
            );
        }
        if (isset($schema->acceptedContentTypes)) {
            $fileInfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $fileInfo->buffer($uploadFile->data) ?: 'application/octet-stream';
            if (!$this->match($schema->acceptedContentTypes, $mimeType)) {
                throw new CustomError(
                    'File content type (' . $mimeType . ') is not in the list of accepted content types (' . implode(', ', $schema->acceptedContentTypes) . ')',
                    ['acceptedContentTypes' => $schema->acceptedContentTypes],
                );
            }
        }
        if (isset($schema->acceptedFileNames)) {
            if (!$this->match($schema->acceptedFileNames, $uploadFile->filename)) {
                throw new CustomError(
                    'Filename (' . $uploadFile->filename . ') is not in the list of accepted file names (' . implode(', ', $schema->acceptedFileNames) . ')',
                    ['acceptedFileNames' => $schema->acceptedFileNames],
                );
            }
        }

        return true;
    }

    /**
     * @param array<string> $patterns Array of strings that may also contain wildcards such as * and ?
     * @param string $subject
     * @return bool
     */
    private function match(array $patterns, string $subject): bool
    {
        foreach ($patterns as $pattern) {
            if (fnmatch($pattern, $subject)) {
                return true;
            }
        }
        return false;
    }

}
