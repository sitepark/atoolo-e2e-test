<?php

declare(strict_types=1);

namespace Atoolo\Resource\Test\Env;

use Atoolo\Resource\Env\EnvVarLoader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(EnvVarLoader::class)]
class EnvVarLoaderTest extends TestCase
{
    private string $baseDir = __DIR__ . '/../resources/Env/EnvVarLoader';

    private string $scriptFileNameBackup;

    public function setUp(): void
    {
        $this->scriptFileNameBackup = $_SERVER['SCRIPT_FILENAME'] ?? null;
    }
    public function tearDown(): void
    {
        unset($_SERVER['RESOURCE_ROOT']);
        $_SERVER['SCRIPT_FILENAME'] = $this->scriptFileNameBackup;
    }

    public function testLoadVarsWithExistsResourceRoot(): void
    {
        $_SERVER['RESOURCE_ROOT'] = 'test';
        $loader = new EnvVarLoader();
        $env = $loader->loadEnvVars();
        $this->assertFalse(
            isset($env['RESOURCE_ROOT']),
            'RESOURCE_ROOT should no set',
        );
    }

    public function testDetermineResourceWithInvalidDir(): void
    {
        $loader = new EnvVarLoader('/invalid-dir');
        $env = $loader->loadEnvVars();
        $this->assertFalse(
            isset($env['RESOURCE_ROOT']),
            'RESOURCE_ROOT should no set',
        );
    }

    public function testDetermineResourceViaScriptFilename(): void
    {
        $_SERVER['SCRIPT_FILENAME'] =
            $this->baseDir . '/hostDir/app/bin/console';
        $loader = new EnvVarLoader('/tmp');
        $env = $loader->loadEnvVars();
        $this->assertEquals(
            $this->baseDir . '/hostDir/resources',
            isset($env['RESOURCE_ROOT']),
            'unexpected RESOURCE_ROOT',
        );
    }

    public function testDetermineResourceRootInHostDir(): void
    {
        $loader = new EnvVarLoader($this->baseDir . '/hostDir');
        $env = $loader->loadEnvVars();
        $expected = [
            'RESOURCE_ROOT' => realpath($this->baseDir . '/hostDir/resources'),
            'RESOURCE_HOST' => 'www.example.com',
        ];
        $this->assertEquals(
            $expected,
            $env,
            'unexpected env',
        );
    }

    public function testDetermineResourceRootInHostDirInvalidContext(): void
    {
        $this->expectException(RuntimeException::class);
        $loader = new EnvVarLoader($this->baseDir . '/hostDir-invalid-context');
        $loader->loadEnvVars();
    }

    public function testDetermineResourceRootInResourceDir(): void
    {
        $loader = new EnvVarLoader($this->baseDir . '/hostDir/resources');
        $env = $loader->loadEnvVars();
        $this->assertEquals(
            $this->baseDir . '/hostDir/resources',
            isset($env['RESOURCE_ROOT']),
            'unexpected RESOURCE_ROOT',
        );
    }

    public function testDetermineResourceRootWithDocumentRootLayout(): void
    {
        $loader = new EnvVarLoader($this->baseDir . '/documentRootLayout');
        $env = $loader->loadEnvVars();
        $this->assertEquals(
            $this->baseDir . '/documentRootLayout',
            isset($env['RESOURCE_ROOT']),
            'unexpected RESOURCE_ROOT',
        );
    }
}
