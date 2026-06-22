<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Test\Composer;

use Atoolo\Runtime\Composer\RuntimeFile;
use Composer\Composer;
use Composer\Config;
use Composer\IO\IOInterface;
use Composer\Package\BasePackage;
use Composer\Package\RootPackageInterface;
use Composer\Repository\InstalledRepositoryInterface;
use Composer\Repository\RepositoryManager;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(RuntimeFile::class)]
class RuntimeFileTest extends TestCase
{
    private string $testDir = __DIR__ . '/../../var/test/RuntimeFile';
    private string $resourceDir = __DIR__
        . '/../resources/Composer/RuntimeFile';

    private string $runtimeFile;

    private Composer&Stub $composer;

    private RepositoryManager&Stub $repositoryManager;

    private InstalledRepositoryInterface&Stub $localRepository;

    public function setUp(): void
    {
        $this->runtimeFile = $this->testDir
            . '/vendor/atoolo_runtime.php';
        $dir = dirname($this->runtimeFile);
        if (
            (is_dir($dir) === false)
            && mkdir($dir, 0777, true) === false
        ) {
            throw new RuntimeException(
                'Failed to create directory: ' . $dir,
            );
        }

        if (is_file($this->runtimeFile)) {
            unlink($this->runtimeFile);
        }

        $config = $this->createStub(Config::class);
        $config->method('get')
            ->willReturn($this->testDir . '/vendor');
        $this->composer = $this->createStub(Composer::class);
        $this->composer->method('getConfig')
            ->willReturn($config);

        $this->repositoryManager = $this->createStub(RepositoryManager::class);
        $this->composer->method('getRepositoryManager')
            ->willReturn($this->repositoryManager);
        $this->localRepository = $this->createStub(
            InstalledRepositoryInterface::class,
        );
        $this->repositoryManager->method('getLocalRepository')
            ->willReturn($this->localRepository);
    }

    public function testGetRuntimeFile(): void
    {
        $runtimeFile = new RuntimeFile($this->composer, '');
        self::assertEquals(
            'vendor/atoolo_runtime.php',
            $runtimeFile->getRuntimeFilePath(),
            'Failed to get runtime file path',
        );
    }

    /**
     * @throws Exception
     */
    public function testCreateRuntimeFile(): void
    {
        $projectDir = $this->testDir
            . '/testCreateRuntimeFile';
        @mkdir($projectDir . '/vendor', 0777, true);

        $runtimeFileTemplate = $this->resourceDir . '/atoolo_runtime.template';

        $config = $this->createStub(Config::class);
        $config->method('get')
            ->willReturn($projectDir . '/vendor');
        $composer = $this->createStub(Composer::class);
        $composer->method('getConfig')
            ->willReturn($config);
        $composer->method('getRepositoryManager')
            ->willReturn($this->repositoryManager);

        $rootPackage = $this->createStub(RootPackageInterface::class);
        $rootPackage->method('getName')
            ->willReturn('test/root-package');
        $rootPackage->method('getExtra')
            ->willReturn([
                'atoolo' => [
                    'runtime' => [
                        'template' => $runtimeFileTemplate,
                        'option' => 'B',
                    ],
                ],
            ]);
        $composer->method('getPackage')
            ->willReturn($rootPackage);
        $this->localRepository->method('getPackages')
            ->willReturn([]);

        $io = $this->createStub(IOInterface::class);

        $runtimeFile = new RuntimeFile($composer, $projectDir);
        $runtimeFile->updateRuntimeFile($io);

        $runtimeFile = $projectDir . '/vendor/atoolo_runtime.php';

        $this->assertEquals(
            <<<EOF
            runtime_class='Atoolo\\\\Runtime\\\\AtooloRuntime'
            project_dir=dirname(__DIR__, 1)
            runtime_options=array (
              'test/root-package' => 
              array (
                'template' => '$runtimeFileTemplate',
                'option' => 'B',
              ),
            )

            EOF,
            file_get_contents($runtimeFile),
            'Unexpected runtime file content',
        );
    }

    public function testNonStringVendorDir(): void
    {
        $io = $this->createStub(IOInterface::class);
        $config = $this->createStub(Config::class);
        $config->method('get')
            ->willReturn([123]);
        $composer = $this->createStub(Composer::class);
        $composer->method('getConfig')
            ->willReturn($config);

        $runtimeFile = new RuntimeFile(
            $composer,
            $this->testDir,
        );
        $this->expectException(RuntimeException::class);
        $runtimeFile->updateRuntimeFile($io);
    }

    public function testInvalidVendorDir(): void
    {
        $io = $this->createStub(IOInterface::class);
        $config = $this->createStub(Config::class);
        $config->method('get')
            ->willReturn('abc');
        $composer = $this->createStub(Composer::class);
        $composer->method('getConfig')
            ->willReturn($config);

        $runtimeFile = new RuntimeFile(
            $composer,
            $this->testDir,
        );
        $this->expectException(RuntimeException::class);
        $runtimeFile->updateRuntimeFile($io);
    }

    public function testUpdateRuntimeFile(): void
    {
        $io = $this->createStub(IOInterface::class);

        $package = $this->createPackage(
            'test/package',
            [
                'atoolo' => [
                    'runtime' => ['option' => 'A'],
                ],
            ],
        );

        $packageWithoutExtra = $this->createPackage(
            'test/package',
            [],
        );

        $this->localRepository->method('getPackages')
            ->willReturn([
                $package,
                $packageWithoutExtra,
            ]);

        $rootPackage = $this->createStub(RootPackageInterface::class);
        $rootPackage->method('getName')
            ->willReturn('test/root-package');
        $rootPackage->method('getExtra')
            ->willReturn([
                'atoolo' => [
                    'runtime' => ['option' => 'B'],
                ],
            ]);
        $this->composer->method('getPackage')
            ->willReturn($rootPackage);

        $runtimeFile = new RuntimeFile(
            $this->composer,
            $this->testDir,
        );
        $runtimeFile->updateRuntimeFile($io);
        $content = file_get_contents($this->runtimeFile);

        $expectedOptions = var_export([
            'test/package' => ['option' => 'A'],
            'test/root-package' => ['option' => 'B'],
        ], true);

        $this->assertStringContainsString(
            $expectedOptions,
            $content,
            'Failed to update runtime file',
        );
    }

    public function testUpdateRuntimeFileWithNonNestedProjectDir(): void
    {
        $io = $this->createStub(IOInterface::class);

        $this->localRepository->method('getPackages')
            ->willReturn([]);

        $rootPackage = $this->createStub(RootPackageInterface::class);
        $this->composer->method('getPackage')
            ->willReturn($rootPackage);
        $runtimeFile = new RuntimeFile(
            $this->composer,
            realpath($this->testDir . '/vendor'),
        );
        $runtimeFile->updateRuntimeFile($io);
        $content = file_get_contents($this->runtimeFile);
        $this->assertStringContainsString(
            '  __' . 'DIR__',
            $content,
            'Failed to update runtime file',
        );
    }

    public function testUpdateRuntimeFileWithInvalidTemplateFile(): void
    {
        $io = $this->createStub(IOInterface::class);
        $rootPackage = $this->createStub(RootPackageInterface::class);
        $rootPackage->method('getName')
            ->willReturn('test/root-package');
        $rootPackage->method('getExtra')
            ->willReturn([
                'atoolo' => [
                    'runtime' => ['template' => 'invalid-path'],
                ],
            ]);
        $this->composer->method('getPackage')
            ->willReturn($rootPackage);

        $runtimeFile = new RuntimeFile(
            $this->composer,
            $this->testDir,
        );

        $this->expectException(InvalidArgumentException::class);
        $runtimeFile->updateRuntimeFile($io);
    }

    public function testUpdateRuntimeFileWithUnreadableTemplateFile(): void
    {

        $nonReadableFile = $this->testDir . '/nonreadable.template';

        $io = $this->createStub(IOInterface::class);
        $rootPackage = $this->createStub(RootPackageInterface::class);
        $rootPackage->method('getName')
            ->willReturn('test/root-package');
        $rootPackage->method('getExtra')
            ->willReturn([
                'atoolo' => [
                    'runtime' => ['template' => 'nonreadable.template'],
                ],
            ]);
        $this->composer->method('getPackage')
            ->willReturn($rootPackage);
        $this->localRepository->method('getPackages')
            ->willReturn([]);

        $runtimeFile = new RuntimeFile(
            $this->composer,
            $this->testDir,
        );


        try {
            touch($nonReadableFile);
            chmod($nonReadableFile, 0000);

            $this->expectException(RuntimeException::class);
            $runtimeFile->updateRuntimeFile($io);
        } finally {
            chmod($nonReadableFile, 0644);
            unlink($nonReadableFile);
        }
    }

    public function testRemoveRuntimeFile(): void
    {
        $io = $this->createStub(IOInterface::class);
        $vendorDir = $this->composer->getConfig()->get('vendor-dir');
        $file = $vendorDir . '/atoolo_runtime.php';
        touch($file);
        $runtimeFile = new RuntimeFile(
            $this->composer,
            $this->testDir,
        );
        $runtimeFile->removeRuntimeFile($io);
        $this->assertFileDoesNotExist(
            $file,
            'Failed to remove runtime file',
        );
    }

    private function createPackage(string $name, mixed $extra): BasePackage
    {
        $package = $this->createStub(BasePackage::class);
        $package->method('getName')
            ->willReturn($name);
        $package->method('getExtra')
            ->willReturn($extra);
        return $package;
    }
}
