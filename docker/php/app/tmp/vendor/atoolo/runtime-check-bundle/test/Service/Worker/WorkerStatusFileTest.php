<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Check\Test\Service\Worker;

use Atoolo\Runtime\Check\Service\CheckStatus;
use Atoolo\Runtime\Check\Service\Platform;
use Atoolo\Runtime\Check\Service\Worker\WorkerStatusFile;
use DateTime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use RuntimeException;

#[CoversClass(WorkerStatusFile::class)]
class WorkerStatusFileTest extends TestCase
{
    private string $resourceDir = __DIR__
        . '/../../resources/Service/Worker/WorkerStatusFileTest';

    private string $testDir = __DIR__
        . '/../../../var/test/WorkerStatusFileTest';

    public function setUp(): void
    {
        if (!is_dir($this->testDir)) {
            mkdir($this->testDir, 0777, true);
        }
    }

    public function tearDown(): void
    {
        if (is_dir($this->testDir)) {
            foreach (scandir($this->testDir) as $file) {
                if ($file === '.' || $file === '..') {
                    continue;
                }
                unlink($this->testDir . '/' . $file);
            }
            rmdir($this->testDir);
        }
    }

    public function testRead(): void
    {
        $date = new DateTime(
            '2024-06-10 13:31:00',
        );
        $platform = $this->createStub(Platform::class);
        $platform->method('time')
            ->willReturn($date->getTimestamp());

        $statusFile = new WorkerStatusFile(
            $this->resourceDir . '/statusFile.json',
            10,
            $platform,
        );
        $status = $statusFile->read();

        $expected = CheckStatus::createSuccess();
        $expected->addReport('scheduler', [
            'last-run' => '10.06.2024 13:16:00',
        ]);

        $this->assertEquals(
            $expected,
            $status,
            'Unexpected status',
        );
    }

    public function testReadWithEmptyStatusFile(): void
    {
        $date = new DateTime(
            '2024-06-10 13:31:00',
        );
        $platform = $this->createStub(Platform::class);
        $platform->method('time')
            ->willReturn($date->getTimestamp());

        $statusFile = new WorkerStatusFile(
            $this->resourceDir . '/empty-statusFile.json',
            10,
            $platform,
        );
        $status = $statusFile->read();

        $expected = CheckStatus::createFailure();
        $expected->addMessage(
            'scheduler',
            'The worker did not run in the last 10 minutes.'
            . ' Last run: unknown',
        );

        $this->assertEquals(
            $expected,
            $status,
            'Unexpected status',
        );
    }
    public function testReadFileNotExists(): void
    {
        $statusFile = new WorkerStatusFile(
            $this->resourceDir . '/no-exists.json',
            10,
        );
        $status = $statusFile->read();

        $expected = CheckStatus::createFailure();
        $expected->addMessage('worker', 'worker not running');

        $this->assertEquals(
            $expected,
            $status,
            'Unexpected status',
        );
    }

    public function testReadFileNotReadable(): void
    {
        $testFile = $this->testDir . '/not-readable';
        touch($testFile);
        chmod($testFile, 0000);
        $this->expectException(RuntimeException::class);
        try {
            $statusFile = new WorkerStatusFile(
                $testFile,
                10,
            );
            $statusFile->read();
        } finally {
            chmod($testFile, 0777);
            unlink($testFile);
        }
    }


    public function testReadFileLastRunExpired(): void
    {
        $date = new DateTime(
            '2024-06-10 13:32:00',
        );
        $platform = $this->createStub(Platform::class);
        $platform->method('time')
            ->willReturn($date->getTimestamp());

        $statusFile = new WorkerStatusFile(
            $this->resourceDir . '/statusFile.json',
            10,
            $platform,
        );
        $status = $statusFile->read();

        $expected = CheckStatus::createFailure();
        $expected->addReport('scheduler', [
            'last-run' => '10.06.2024 13:16:00',
        ]);
        $expected->addMessage(
            'scheduler',
            'The worker did not run in the last 10 minutes.'
                . ' Last run: 10.06.2024 13:16:00',
        );

        $this->assertEquals(
            $expected,
            $status,
            'Unexpected status',
        );
    }

    public function testReadWithInvalidLastRun(): void
    {
        $date = new DateTime(
            '2024-06-10 13:16:00',
        );
        $platform = $this->createStub(Platform::class);
        $platform->method('time')
            ->willReturn($date->getTimestamp());

        $statusFile = new WorkerStatusFile(
            $this->resourceDir . '/statusFileWithInvalidLastRun.json',
            10,
            $platform,
        );
        $status = $statusFile->read();

        $expected = CheckStatus::createFailure();
        $expected->addReport('scheduler', [
            'last-run' => 123,
        ]);
        $expected->addMessage(
            'scheduler',
            'The worker did not run in the last 10 minutes.'
            . ' Last run: 123',
        );

        $this->assertEquals(
            $expected,
            $status,
            'Unexpected status',
        );
    }

    /**
     * @throws Exception
     * @throws \JsonException
     */
    public function testWrite(): void
    {

        $date = new DateTime(
            '2024-06-10 13:32:00',
        );
        $platform = $this->createStub(Platform::class);
        $platform->method('time')
            ->willReturn($date->getTimestamp());

        $file = $this->testDir . '/statusFile.json';
        $statusFile = new WorkerStatusFile(
            $file,
            10,
            $platform,
        );

        $checkStatus = CheckStatus::createSuccess();
        $checkStatus->addReport('test', [
            'a' => 'b',
        ]);

        try {
            $_SERVER['SUPERVISOR_ENABLED'] = '1';
            $_SERVER['SUPERVISOR_GROUP_NAME'] = 'worker';
            $_SERVER['SUPERVISOR_PROCESS_NAME'] = 'worker_01';
            $statusFile->write($checkStatus);
        } finally {
            unset($_SERVER['SUPERVISOR_ENABLED']);
            unset($_SERVER['SUPERVISOR_GROUP_NAME']);
            unset($_SERVER['SUPERVISOR_PROCESS_NAME']);
        }

        $writtenData = json_decode(
            file_get_contents($file),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        $this->assertEquals(
            [
                'success' => true,
                'reports' => [
                    'test' => [
                        'a' => 'b',
                    ],
                    'supervisor' => [
                        'group' => 'worker',
                        'process' => 'worker_01',
                    ],
                    'scheduler' => [
                        'last-run' => '10.06.2024 13:32:00',
                    ],
                ],
            ],
            $writtenData,
            'Unexpected status',
        );
    }
}
