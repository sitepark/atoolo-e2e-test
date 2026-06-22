<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Test\Service;

use Atoolo\Resource\DataBag;
use Atoolo\Resource\ResourceChannel;
use Atoolo\Resource\ResourceTenant;
use Atoolo\WebAccount\Dto\Config\WebAccountConfiguration;
use Atoolo\WebAccount\Service\ConfigurationLoader;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[CoversClass(ConfigurationLoader::class)]
class ConfigurationLoaderTest extends TestCase
{
    private readonly ResourceChannel $resourceChannel;

    private readonly DenormalizerInterface&MockObject $denormalizer;

    private readonly ConfigurationLoader $configurationLoader;

    private string $tempDir;

    protected function setUp(): void
    {
        $this->tempDir = sys_get_temp_dir() . '/web-account-test-' . uniqid();
        mkdir($this->tempDir . '/web-account', 0777, true);

        $tenant = new ResourceTenant(
            id: 'test-tenant',
            name: 'Test Tenant',
            anchor: '/test',
            host: 'test.example.com',
            attributes: new DataBag([]),
        );

        $this->resourceChannel = new ResourceChannel(
            id: 'test-channel',
            name: 'Test Channel',
            anchor: '/test',
            serverName: 'test.example.com',
            isPreview: false,
            nature: 'web',
            locale: 'en_US',
            baseDir: $this->tempDir,
            resourceDir: $this->tempDir . '/resources',
            configDir: $this->tempDir,
            searchIndex: 'test-index',
            translationLocales: ['en_US', 'de_DE'],
            attributes: new DataBag([]),
            tenant: $tenant,
        );

        $this->denormalizer = $this->createMock(DenormalizerInterface::class);

        $this->configurationLoader = new ConfigurationLoader(
            $this->resourceChannel,
            $this->denormalizer,
        );
    }

    protected function tearDown(): void
    {
        if (is_dir($this->tempDir)) {
            $this->removeDirectory($this->tempDir);
        }
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir) ?: [], ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    public function testLoadConfigurationSuccessfully(): void
    {
        $configFile = $this->tempDir . '/web-account/test.php';
        file_put_contents($configFile, "<?php\nreturn ['name' => 'test', 'apiKey' => 'key123'];");

        $expectedConfig = $this->createMock(WebAccountConfiguration::class);

        $this->denormalizer
            ->expects($this->once())
            ->method('denormalize')
            ->with(
                ['name' => 'test', 'apiKey' => 'key123'],
                WebAccountConfiguration::class,
                'json',
            )
            ->willReturn($expectedConfig);

        $result = $this->configurationLoader->load('test');

        $this->assertSame($expectedConfig, $result, "The loaded configuration should match the expected configuration");
    }

    public function testLoadThrowsExceptionWhenFileNotFound(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Configuration 'nonexistent' not found.");

        $this->configurationLoader->load('nonexistent');
    }

    public function testLoadThrowsExceptionWhenFileDoesNotReturnArray(): void
    {
        $configFile = $this->tempDir . '/web-account/invalid.php';
        file_put_contents($configFile, "<?php\nreturn 'not an array';");

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('should return an array');

        $this->configurationLoader->load('invalid');
    }

    public function testLoadThrowsExceptionOnDeserializationError(): void
    {
        $configFile = $this->tempDir . '/web-account/test.php';
        file_put_contents($configFile, "<?php\nreturn ['name' => 'test'];");

        $denormalizerException = new class ('Deserialization failed') extends \Exception implements ExceptionInterface {};

        $this->denormalizer
            ->method('denormalize')
            ->willThrowException($denormalizerException);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Failed to deserialize web-account configuration');

        $this->configurationLoader->load('test');
    }

    public function testLoadSanitizesFileName(): void
    {
        $configFile = $this->tempDir . '/web-account/sanitized-name.php';
        file_put_contents($configFile, "<?php\nreturn ['name' => 'test'];");

        $expectedConfig = $this->createMock(WebAccountConfiguration::class);
        $this->denormalizer->method('denormalize')->willReturn($expectedConfig);

        $result = $this->configurationLoader->load('sanitized name!@#$%');

        $this->assertSame($expectedConfig, $result, "The configuration loader should sanitize file names correctly");
    }
}
