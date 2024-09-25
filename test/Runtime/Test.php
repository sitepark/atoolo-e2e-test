<?php

declare(strict_types=1);

namespace Atoolo\E2E\Test\Runtime;

use Atoolo\E2E\Test\TokenGenerator;
use JsonException;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\Process\Process;

class Test extends TestCase
{
    private static string $ENDPOINT_BASE;

    public static function setUpBeforeClass(): void
    {
        // from phpunit.xml
        self::$ENDPOINT_BASE = $_SERVER['ENDPOINT_BASE'];
    }

    /**
     * @throws JsonException
     */
    public function testRuntimeCheckPerHttp(): void
    {
        $token = TokenGenerator::getInstance()->getToken();
        $opts = [
            'http' => [
                'header' => 'Authorization: Bearer ' . $token,
            ],
        ];
        $url = self::$ENDPOINT_BASE . '/api/runtime-check';

        $content = file_get_contents(
            $url,
            false,
            stream_context_create($opts),
        );
        if ($content === false) {
            throw new RuntimeException((error_get_last())['message'] ?? 'unknown error');
        }
        /**
         * @var array{success: bool} $data
         */
        $data = json_decode(
            $content,
            true,
            512,
            JSON_THROW_ON_ERROR,
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
            '/apps/atoolo-e2e-test/bin/console runtime:check --json --fail-on-error false --skip fpm-fcgi',
        ]);

        $process->run();
        if (!$process->isSuccessful()) {
            $this->fail($process->getErrorOutput());
        }

        /** @var array{messages?:array<string>, success:bool} $data */
        $data = json_decode(
            $process->getOutput(),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        $messages = isset($data['messages'])
            ? implode("\n", $data['messages'])
            : '';

        $this->assertTrue(
            $data['success'],
            "Runtime check failed\n" . $messages,
        );
    }
}
