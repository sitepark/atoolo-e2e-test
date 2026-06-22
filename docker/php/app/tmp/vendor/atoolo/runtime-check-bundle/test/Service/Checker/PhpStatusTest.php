<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Check\Test\Service\Checker;

use Atoolo\Runtime\Check\Service\Checker\PhpStatus;
use Atoolo\Runtime\Check\Service\CheckStatus;
use Atoolo\Runtime\Check\Service\Platform;
use JsonException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use RuntimeException;

function fpm_get_status(): array
{
    return [];
}

#[CoversClass(PhpStatus::class)]
class PhpStatusTest extends TestCase
{
    private string $resourceDir = __DIR__
        . '/../../resources/Service/Checker/PhpStatusTest';

    private Platform $platform;

    public function setUp(): void
    {
        $this->platform = $this->createStub(Platform::class);
        $this->platform->method('getPhpIniLoadedFile')
            ->willReturn('php.ini');
        $this->platform->method('getIni')
            ->willReturnMap([
                ['date.timezone', 'Timezone'],
            ]);
        $this->platform->method('getVersion')
            ->willReturn('8.3.0');
        $this->platform->method('getOpcacheGetStatus')
            ->willReturn([
                'memory_usage' => '123',
                'other' => 'test',
            ]);
        $this->platform->method('getFpmPoolStatus')
            ->willReturn([
                'pool' => 'www',
                'other' => 'test',
            ]);
    }

    /**
     * @throws \JsonException
     */
    public function testGetStatus(): void
    {
        $phpStatus = new PhpStatus(
            $this->resourceDir . '/phpStatus.json',
            'fpm-fcgi',
            $this->platform,
        );
        $status = $phpStatus->check();

        $expected = CheckStatus::createSuccess();
        $expected->addReport('php', [
            'version' => '8.3.0',
            'ini' => [
                'file' => 'php.ini',
                'date.timezone' => 'Timezone',
            ],
            'fpm' => [
                'config' => [
                    'section' => [
                        'key' => 'value',
                        'includetest' => 'test',
                    ],
                    'global' => [
                        'include' => 'fpm/conf.d/*.conf',
                    ],
                ],
                'status' => [
                    'pool' => 'www',
                ],
            ],
            'opcache' => [
                'memory_usage' => '123',
            ],
        ]);
        $this->assertEquals(
            $expected,
            $status,
            'Unexpected status',
        );
    }

    public function testGetStatusUnreadablePhpFpmConf(): void
    {
        $this->expectException(RuntimeException::class);
        $phpStatus = new PhpStatus(
            $this->resourceDir . '/phpStatus-unreadable-php-fpm-conf.json',
            'fpm-fcgi',
            $this->platform,
        );
        $phpStatus->check();
    }

    /**
     * @throws JsonException
     */
    public function testGetStatusUnreadablePhpFpmConfInclude(): void
    {
        $this->expectException(RuntimeException::class);
        $phpStatus = new PhpStatus(
            $this->resourceDir
            . '/phpStatus-unreadable-php-fpm-conf-include.json',
            'fpm-fcgi',
            $this->platform,
        );
        $phpStatus->check();
    }

    public function testGetStatusWithEmptyConfig(): void
    {
        $phpStatus = new PhpStatus(
            $this->resourceDir . '/empty-phpStatus.json',
            'fpm-fcgi',
            $this->platform,
        );
        $status = $phpStatus->check();
        $expected = CheckStatus::createSuccess();
        $expected->addReport('php', [
            'version' => '8.3.0',
            'ini' => [
                'file' => 'php.ini',
            ],
            'fpm' => [
                'config' => [
                ],
                'status' => [
                ],
            ],
            'opcache' => [
            ],
        ]);

        $this->assertEquals(
            $expected,
            $status,
            'Unexpected status',
        );
    }

    /**
     * @throws JsonException
     */
    public function testGetStatusWithUnreadableConfig(): void
    {
        $this->expectException(RuntimeException::class);
        new PhpStatus(
            $this->resourceDir . '/non-exists-phpStatus.json',
            'fpm-fcgi',
            $this->platform,
        );
    }
}
