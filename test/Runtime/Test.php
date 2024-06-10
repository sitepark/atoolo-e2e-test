<?php

declare(strict_types=1);

namespace Atoolo\E2E\Test\Runtime;

use Atoolo\Runtime\Check\Service\CheckStatus;
use JsonException;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Process;

class Test extends TestCase
{
    private static string $ENDPOINT_BASE;

    public static function setUpBeforeClass(): void
    {
        // from phpunit.xml
        self::$ENDPOINT_BASE = $_SERVER['ENTPOINT_BASE'];
    }

    /**
     * @throws JsonException
     */
    public function testRuntimeCheckPerHttp(): void
    {
        $content = file_get_contents(self::$ENDPOINT_BASE . '/runtime-check')
            ?? '{}';
        $data = json_decode(
            $content,
            true,
            512,
            JSON_THROW_ON_ERROR
        );
        $this->assertTrue($data['success'], 'Runtime check failed');
    }

    /**
     * @throws JsonException
     */
    public function testRuntimeCheckPerConsole(): void
    {
        $process = new Process([
            'docker',
            'compose',
            'exec',
            '-u',
            'root',
            'php',
            'su',
            'www-data',
            '-s',
            '/bin/bash',
            '-c',
            '/apps/atoolo-e2e-test/bin/console runtime:check --json --fpm-skip'
        ]);

        $process->run();
        if (!$process->isSuccessful()) {
            $this->fail($process->getErrorOutput());
        }

        /** @var array<string, mixed> $data */
        $data = json_decode(
            $process->getOutput(),
            true,
            512,
            JSON_THROW_ON_ERROR
        );

        $this->assertTrue(
            $data['success'],
            "Runtime check failed\n" . print_r($data['messages'], true)
        );
    }
}