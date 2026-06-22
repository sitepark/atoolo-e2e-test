<?php

declare(strict_types=1);

namespace Atoolo\Form\Service;

use Atoolo\Form\Dto\FormData\UploadFile;
use Atoolo\Form\Exception\DataUrlException;

class DataUrlParser
{
    public function parse(string $dataUrl): UploadFile
    {
        if (!preg_match('/^data:([^;]+);(.*)$/', $dataUrl, $matches)) {
            throw new DataUrlException('invalid data-url.');
        }

        $mimeType = $matches[1];
        $parameterString = $matches[2];

        $parameters = [];
        $base64Data = '';

        foreach (explode(';', $parameterString) as $parameter) {
            if (str_starts_with($parameter, 'base64,')) {
                $base64Data = substr($parameter, 7);
            } else {
                [$key, $value] = explode('=', $parameter, 2);
                $parameters[$key] = urldecode($value);
            }
        }

        $binaryData = base64_decode($base64Data, true);
        if ($binaryData === false) {
            throw new DataUrlException('invalid base64');
        }

        return new UploadFile(
            filename: $parameters['name'] ?? '',
            contentType: $mimeType,
            data: $binaryData,
            size: mb_strlen($binaryData, '8bit'),
        );
    }
}
