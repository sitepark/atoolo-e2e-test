<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Check\Test\Service\Checker;

use Atoolo\Runtime\Check\Service\Checker\ProcessStatus;
use Atoolo\Runtime\Check\Service\CheckStatus;
use Atoolo\Runtime\Check\Service\Platform;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ProcessStatus::class)]
class ProcessStatusTest extends TestCase
{
    private string $originScriptFilename;

    private Platform $platform;

    public function tearDown(): void
    {
        $_SERVER['SCRIPT_FILENAME'] = $this->originScriptFilename;
    }

    public function setUp(): void
    {
        $this->originScriptFilename = $_SERVER['SCRIPT_FILENAME'] ?? '';
        $_SERVER['SCRIPT_FILENAME'] = '/path/to/bin/console';
        $this->platform = $this->createStub(Platform::class);
        $this->platform->method('getUser')
            ->willReturn('user');
        $this->platform->method('getGroup')
            ->willReturn('group');
    }

    public function testGetStatus(): void
    {
        $processStatus = new ProcessStatus(
            $this->platform,
        );
        $status = $processStatus->check();

        $expected = CheckStatus::createSuccess();
        $expected->addReport('process', [
            'user' => 'user',
            'group' => 'group',
            'script' => '/path/to/bin/console',
        ]);
        $this->assertEquals(
            $expected,
            $status,
            'Unexpected status',
        );
    }
}
