<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Test\Composer;

use Atoolo\Runtime\Composer\ComposerJson;
use Atoolo\Runtime\Composer\ComposerJsonFactory;
use Composer\Composer;
use Composer\Package\RootPackageInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(ComposerJson::class)]
class ComposerJsonTest extends TestCase
{
    private string $testDir = __DIR__
        . '/../../var/test/ComposerJson/';
    private string $resourceDir = __DIR__
        . '/../resources/Composer/ComposerJson';

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

    private function createComposerJson(
        string $composerFilePath,
    ): ComposerJson {
        $factory = new ComposerJsonFactory();
        $rootPackage = $this->createStub(RootPackageInterface::class);
        $composer = $this->createStub(Composer::class);
        $composer->method('getPackage')
            ->willReturn($rootPackage);
        return $factory->create(
            $composer,
            $composerFilePath,
        );
    }

    public function testGetPath(): void
    {
        $composerFilePath = $this->resourceDir . '/valid-composer.json';
        $composerJson = $this->createComposerJson($composerFilePath);
        self::assertEquals(
            realpath($composerFilePath),
            $composerJson->getPath(),
            'Failed to resolve composer.json file: composer.json',
        );
    }

    public function testGetJsonContent(): void
    {
        $composerFilePath = $this->resourceDir . '/valid-composer.json';
        $composerJson = $this->createComposerJson($composerFilePath);
        $this->assertEquals(
            [
                'name' => 'atoolo/runtime',
                'description' => 'valid composer.json test file',
            ],
            $composerJson->getJsonContent(),
            'Unexpected JSON content',
        );
    }

    public function testAddAutoloadFile(): void
    {
        $file = $this->createTestFile(
            'composer-add-autoload-file.json',
            [
                'name' => 'atoolo/runtime',
            ],
        );

        $composerJson = $this->createComposerJson($file);
        $composerJson->addAutoloadFile('vendor/test.php');

        $expected = [
            'name' => 'atoolo/runtime',
            'autoload' => [
                'files' => ['vendor/test.php'],
            ],
        ];

        $this->assertEquals(
            $expected,
            json_decode(
                file_get_contents($file),
                true,
                512,
                JSON_THROW_ON_ERROR,
            ),
            'Failed to add autoload file to composer.json',
        );
    }

    public function testAddAutoloadFileWithExistsFile(): void
    {
        $file = $this->createTestFile(
            'composer-add-autoload-file-with-exists-file.json',
            [
                'name' => 'atoolo/runtime',
                'autoload' => [
                    'files' => ['vendor/test.php'],
                ],
            ],
        );

        $composerJson = $this->createComposerJson($file);
        $composerJson->addAutoloadFile('vendor/test.php');

        $expected = [
            'name' => 'atoolo/runtime',
            'autoload' => [
                'files' => ['vendor/test.php'],
            ],
        ];

        $this->assertEquals(
            $expected,
            json_decode(
                file_get_contents($file),
                true,
                512,
                JSON_THROW_ON_ERROR,
            ),
            'Failed to add autoload file to composer.json',
        );
    }

    public function testRemoveAutoloadFile(): void
    {
        $file = $this->createTestFile(
            'composer-remove-autoload-file.json',
            [
                'name' => 'atoolo/runtime',
                'autoload' => [
                    'files' => ['vendor/test.php'],
                ],
            ],
        );
        $composerJson = $this->createComposerJson($file);
        $composerJson->removeAutoloadFile('vendor/test.php');

        $expected = [
            'name' => 'atoolo/runtime',
            'autoload' => [],
        ];

        $this->assertEquals(
            $expected,
            json_decode(
                file_get_contents($file),
                true,
                512,
                JSON_THROW_ON_ERROR,
            ),
            'Failed to add autoload file to composer.json',
        );
    }

    public function testRemoveAutoloadFileWithoutFile(): void
    {
        $file = $this->createTestFile(
            'composer-remove-autoload-file.json',
            [
                'name' => 'atoolo/runtime',
                'autoload' => [],
            ],
        );

        $composerJson = $this->createComposerJson($file);
        $composerJson->removeAutoloadFile('vendor/test.php');

        $expected = [
            'name' => 'atoolo/runtime',
            'autoload' => [],
        ];

        $this->assertEquals(
            $expected,
            json_decode(
                file_get_contents($file),
                true,
                512,
                JSON_THROW_ON_ERROR,
            ),
            'Failed to add autoload file to composer.json',
        );
    }

    public function testRemoveAutoloadFileWithOtherAutoloads(): void
    {
        $file = $this->createTestFile(
            'composer-remove-autoload-file.json',
            [
                'name' => 'atoolo/runtime',
                'autoload' => [
                    'files' => [
                        'test/abc.php',
                        'vendor/test.php',
                    ],
                ],
            ],
        );

        $composerJson = $this->createComposerJson($file);
        $composerJson->removeAutoloadFile('vendor/test.php');

        $expected = [
            'name' => 'atoolo/runtime',
            'autoload' => [
                'files' => [
                    'test/abc.php',
                ],
            ],
        ];

        $this->assertEquals(
            $expected,
            json_decode(
                file_get_contents($file),
                true,
                512,
                JSON_THROW_ON_ERROR,
            ),
            'Failed to add autoload file to composer.json',
        );
    }

    private function createTestFile(string $filenname, array $content): string
    {
        $file = $this->testDir . '/' . $filenname;
        file_put_contents(
            $file,
            json_encode($content, JSON_THROW_ON_ERROR),
        );

        return $file;
    }
}
