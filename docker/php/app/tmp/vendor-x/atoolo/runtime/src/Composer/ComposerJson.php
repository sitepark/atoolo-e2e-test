<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Composer;

use Composer\Composer;
use Composer\Json\JsonFile;
use Composer\Json\JsonManipulator;
use JsonException;

class ComposerJson
{
    private JsonFile $jsonFile;

    /**
     * @param array{
     *   autoload?: array{
     *     files?: array<string>
     *   }
     * } $jsonContent
     */
    public function __construct(
        private readonly Composer $composer,
        string $composerJsonFile,
        private readonly JsonManipulator $manipulator,
        private array $jsonContent = [],
    ) {
        $this->jsonFile = new JsonFile($composerJsonFile);
    }

    public function getPath(): string
    {
        return $this->jsonFile->getPath();
    }

    /**
     * @return array{
     *   autoload?: array{
     *     files?: array<string>
     *   }
     * }
     */
    public function getJsonContent(): array
    {
        return $this->jsonContent;
    }

    /**
     * @throws JsonException
     */
    public function addAutoloadFile(string $autoloadFile): bool
    {
        $autoloadFiles = $this->jsonContent['autoload']['files'] ?? [];

        if (in_array($autoloadFile, $autoloadFiles, true)) {
            return false;
        }
        $autoloadFiles[] = $autoloadFile;
        $this->manipulator->addSubNode('autoload', 'files', $autoloadFiles);
        $this->jsonContent['autoload']['files'] = $autoloadFiles;

        file_put_contents(
            $this->jsonFile->getPath(),
            $this->manipulator->getContents(),
        );

        $this->updateAutoloadConfig();

        return true;
    }

    public function removeAutoloadFile(string $autoloadFile): bool
    {
        $autoloadFiles = $this->jsonContent['autoload']['files'] ?? [];

        $key = array_search($autoloadFile, $autoloadFiles, true);
        if ($key === false) {
            return false;
        }
        unset($autoloadFiles[$key]);
        if (empty($autoloadFiles)) {
            $this->manipulator->removeSubNode('autoload', 'files');
            unset($this->jsonContent['autoload']['files']);
        } else {
            $this->manipulator->addSubNode('autoload', 'files', $autoloadFiles);
            $this->jsonContent['autoload']['files'] = $autoloadFiles;
        }
        file_put_contents(
            $this->jsonFile->getPath(),
            $this->manipulator->getContents(),
        );

        $this->updateAutoloadConfig();

        return true;
    }

    public function updateAutoloadConfig(): void
    {
        $this->composer->getPackage()->setAutoload(
            $this->jsonContent['autoload'] ?? [],
        );
    }
}
