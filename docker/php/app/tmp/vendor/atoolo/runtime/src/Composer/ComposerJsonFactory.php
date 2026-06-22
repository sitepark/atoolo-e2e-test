<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Composer;

use Composer\Composer;
use Composer\Json\JsonManipulator;
use JsonException;
use RuntimeException;

class ComposerJsonFactory
{
    public function create(
        Composer $composer,
        string $composerJsonFile,
    ): ComposerJson {

        $composerJsonRealPath = realpath($composerJsonFile);
        if ($composerJsonRealPath === false) {
            throw new RuntimeException(
                "Failed to resolve composer.json file: $composerJsonFile",
            );
        }

        $content = $this->loadContent($composerJsonRealPath);
        $jsonContent = $this->parseJson($composerJsonRealPath, $content);
        return new ComposerJson(
            $composer,
            $composerJsonRealPath,
            new JsonManipulator($content),
            $jsonContent,
        );
    }

    /**
     * @throws JsonException
     */
    private function loadContent(string $composerJsonFile): string
    {
        $content = @file_get_contents($composerJsonFile);
        if ($content === false) {
            throw new RuntimeException(
                "Failed to read composer.json file: $composerJsonFile",
            );
        }
        return $content;
    }

    /**
     * @return array{
     *   autoload?: array{
     *     files?: array<string>
     *   }
     * }
     * @throws JsonException
     */
    private function parseJson(string $composerJsonFile, string $content): array
    {
        $jsonContent = json_decode(
            $content,
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        if (!is_array($jsonContent)) {
            throw new JsonException(
                "Failed to parse composer.json file: $composerJsonFile",
            );
        }

        return $jsonContent;
    }
}
