<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Test\Composer;

use Atoolo\Runtime\Composer\ComposerJsonFactory;
use Composer\Composer;
use JsonException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(ComposerJsonFactory::class)]
class ComposerJsonFactoryTest extends TestCase
{
    private string $testDir = __DIR__
        . '/../../var/test/ComposerJsonFactory/';

    private string $resourceDir = __DIR__
        . '/../resources/Composer/ComposerJsonFactory';

    public function setUp(): void
    {
        if (is_dir($this->testDir) === false) {
            if (mkdir($this->testDir, 0777, true) === false) {
                throw new RuntimeException(
                    'Failed to create directory: ' . $this->testDir,
                );
            }
        }
    }

    public function testCreateWithValidComposerFile(): void
    {
        $factory = new ComposerJsonFactory();
        $composer = $this->createStub(Composer::class);
        $composerJson = $factory->create(
            $composer,
            $this->resourceDir . '/valid-composer.json',
        );
        $this->assertEquals(
            [
                'name' => 'atoolo/runtime',
                'description' => 'valid composer.json test file',
            ],
            $composerJson->getJsonContent(),
            'Unexpected JSON content',
        );
    }

    public function testCreateWithInvalidComposerFile(): void
    {
        $factory = new ComposerJsonFactory();
        $composer = $this->createStub(Composer::class);
        $this->expectException(RuntimeException::class);
        $factory->create(
            $composer,
            $this->resourceDir . '/notfound.json',
        );
    }

    /**
     * @throws Exception
     */
    public function testCreateWithNonReadableFile(): void
    {
        $nonReadableFile = $this->testDir . '/notreadable.json';
        try {
            touch($nonReadableFile);
            chmod($nonReadableFile, 0000);
            $this->expectException(RuntimeException::class);
            $composer = $this->createStub(Composer::class);
            $factory = new ComposerJsonFactory();
            $factory->create(
                $composer,
                $nonReadableFile,
            );
        } finally {
            chmod($nonReadableFile, 0644);
            unlink($nonReadableFile);
        }
    }

    public function testCreateWithInvalidJson(): void
    {
        $this->expectException(JsonException::class);
        $composer = $this->createStub(Composer::class);
        $factory = new ComposerJsonFactory();
        $factory->create(
            $composer,
            $this->resourceDir . '/string.txt',
        );
    }
}
