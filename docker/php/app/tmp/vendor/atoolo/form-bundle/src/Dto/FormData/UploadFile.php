<?php

declare(strict_types=1);

namespace Atoolo\Form\Dto\FormData;

/**
 * @codeCoverageIgnore
 */
class UploadFile
{
    public function __construct(
        public readonly string $filename,
        public readonly string $contentType,
        public readonly string $data,
        public readonly int $size,
    ) {}
}
