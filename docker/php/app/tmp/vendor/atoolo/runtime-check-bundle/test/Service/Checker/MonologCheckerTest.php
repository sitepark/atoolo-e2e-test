<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Check\Test\Service\Checker;

use Atoolo\Runtime\Check\Service\Checker\MonologChecker;
use Atoolo\Runtime\Check\Service\CheckStatus;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

#[CoversClass(MonologChecker::class)]
class MonologCheckerTest extends TestCase
{
    private string $resourceDir = __DIR__
        . '/../../resources/Service/Checker/MonologCheckerTest';

    private string $testDir = __DIR__
        . '/../../../var/test/MonologCheckerTest';

    public function setUp(): void
    {
        if (!is_dir($this->testDir)) {
            if (!mkdir($this->testDir, 0777, true)) {
                throw new \RuntimeException('Cannot create test directory');
            }
        }
    }

    /**
     * @throws Exception
     */
    public function testCheckWithWrongLogger(): void
    {
        $logger = $this->createStub(LoggerInterface::class);
        $checker = new MonologChecker('', '', 0, $logger);
        $status = $checker->check([]);

        $expected = CheckStatus::createFailure();
        $expected->addMessage(
            'logging',
            'unknown: unsupported logger ' . get_class($logger),
        );
        $this->assertEquals(
            $expected,
            $status,
            'Status is not as expected',
        );
    }

    /**
     * @throws Exception
     */
    public function testCheckWithoutStreamHandler(): void
    {
        $logger = $this->createStub(Logger::class);
        $checker = new MonologChecker('', '', 0, $logger);
        $status = $checker->check([]);

        $expected = CheckStatus::createFailure();
        $expected->addMessage(
            'logging',
            'unknown: no stream handler found',
        );
        $this->assertEquals(
            $expected,
            $status,
            'Status is not as expected',
        );
    }

    public function testCheckWithoutLogfile(): void
    {
        $logger = $this->createStub(Logger::class);
        $handler = $this->createStub(StreamHandler::class);
        $handler->method('getUrl')->willReturn(null);
        $handler->method('getLevel')->willReturn(Level::Warning);
        $logger->method('getHandlers')->willReturn([$handler]);

        $checker = new MonologChecker('', '', 0, $logger);
        $status = $checker->check([]);

        $expected = CheckStatus::createFailure();
        $expected->addMessage(
            'logging',
            'logfile not set',
        );
        $expected = CheckStatus::createFailure();
        $expected->addReport('logging', [
            'handler' => [
                [
                    'logfile' => null,
                    'level' => 'WARNING',
                ],
            ],
        ]);
        $expected->addMessage(
            'logging',
            'logfile not set',
        );
        $this->assertEquals(
            $expected,
            $status,
            'Status is not as expected',
        );
    }

    /**
     * @throws Exception
     */
    public function testCheckLogfileNotWritable(): void
    {
        $filesystem = new Filesystem();
        $file = $this->testDir . '/non-writable.log';
        $filesystem->touch($file);
        $filesystem->chmod($file, 0444);

        try {
            $handler = $this->createStub(StreamHandler::class);
            $handler->method('getUrl')->willReturn($file);
            $handler->method('getLevel')->willReturn(Level::Warning);
            $logger = $this->createStub(Logger::class);
            $logger->method('getHandlers')->willReturn([$handler]);

            $checker = new MonologChecker('', '', 0, $logger);
            $status = $checker->check([]);

            $expected = CheckStatus::createFailure();
            $expected->addReport('logging', [
                'handler' => [
                    [
                        'logfile' => $file,
                        'level' => 'WARNING',
                        'logfile-size' => 0,
                        'logdir-size' => 0,
                        'logfile-rotations' => 0,
                    ],
                ],
            ]);
            $expected->addMessage(
                'logging',
                'logfile not writable: ' . $file,
            );
            $this->assertEquals(
                $expected,
                $status,
                'Status is not as expected',
            );
        } finally {
            $filesystem->chmod($file, 0666);
            $filesystem->remove($file);
        }
    }

    public function testCheckDirNotCreateable(): void
    {
        $filesystem = new Filesystem();
        $dir = $this->testDir . '/not-writable/not-createable/logging.log';
        $file = $dir . '/not-createable/logging.log';
        $filesystem->mkdir($dir);
        $filesystem->chmod($dir, 0444);
        try {
            $handler = $this->createStub(StreamHandler::class);
            $handler->method('getUrl')->willReturn($file);
            $handler->method('getLevel')->willReturn(Level::Warning);
            $logger = $this->createStub(Logger::class);
            $logger->method('getHandlers')->willReturn([$handler]);

            $checker = new MonologChecker('', '', 0, $logger);
            $status = $checker->check([]);

            $expected = CheckStatus::createFailure();
            $expected->addReport('logging', [
                'handler' => [
                    [
                        'logfile' => $file,
                        'level' => 'WARNING',
                    ],
                ],
            ]);
            $expected->addMessage(
                'logging',
                'log directory cannot be created: ' . dirname($file),
            );
            $this->assertEquals(
                $expected,
                $status,
                'Status is not as expected',
            );
        } finally {
            $filesystem->chmod($dir, 0777);
            $filesystem->remove($dir);
        }
    }

    public function testCheckFileNotCreateable(): void
    {
        $filesystem = new Filesystem();
        $dir = $this->testDir . '/not-writable';
        $file = $dir . '/logging.log';
        $filesystem->mkdir($dir);
        $filesystem->chmod($dir, 0444);

        try {
            $handler = $this->createStub(StreamHandler::class);
            $handler->method('getUrl')->willReturn($file);
            $handler->method('getLevel')->willReturn(Level::Warning);
            $logger = $this->createStub(Logger::class);
            $logger->method('getHandlers')->willReturn([$handler]);

            $checker = new MonologChecker('', '', 0, $logger);
            $status = $checker->check([]);

            $expected = CheckStatus::createFailure();
            $expected->addReport('logging', [
                'handler' => [
                    [
                        'logfile' => $file,
                        'level' => 'WARNING',
                    ],
                ],
            ]);
            $expected->addMessage(
                'logging',
                'logfile cannot be created: ' . $file,
            );
            $this->assertEquals(
                $expected,
                $status,
                'Status is not as expected',
            );
        } finally {
            $filesystem->chmod($dir, 0777);
            $filesystem->remove($dir);
        }
    }

    public function testCheckSuccessfully(): void
    {
        $file = $this->resourceDir . '/logging.log';
        $handler = $this->createStub(StreamHandler::class);
        $handler->method('getUrl')->willReturn($file);
        $handler->method('getLevel')->willReturn(Level::Warning);
        $logger = $this->createStub(Logger::class);
        $logger->method('getHandlers')->willReturn([$handler]);

        $checker = new MonologChecker('10M', '10X', 0, $logger);
        $status = $checker->check([]);

        $expected = CheckStatus::createSuccess();
        $expected->addReport('logging', [
            'handler' => [
                [
                    'logfile' => $file,
                    'level' => 'WARNING',
                    'logfile-size' => 18,
                    'logdir-size' => 70,
                    'logfile-rotations' => 4,
                ],
            ],
        ]);
        $this->assertEquals(
            $expected,
            $status,
            'Status is not as expected',
        );
    }

    public function testWithFingersCrossedHandler(): void
    {
        $file = $this->resourceDir . '/logging.log';


        $handler = $this->createStub(StreamHandler::class);
        $handler->method('getUrl')->willReturn($file);
        $handler->method('getLevel')->willReturn(Level::Warning);

        $fingersCrossedHandler = $this->createStub(
            FingersCrossedHandler::class,
        );
        $fingersCrossedHandler->method('getHandler')
            ->willReturn($handler);

        $logger = $this->createStub(Logger::class);
        $logger->method('getHandlers')->willReturn([$fingersCrossedHandler]);

        $checker = new MonologChecker('', '', 0, $logger);
        $status = $checker->check([]);

        $expected = CheckStatus::createSuccess();
        $expected->addReport('logging', [
            'handler' => [
                [
                    'logfile' => $file,
                    'level' => 'WARNING',
                    'logfile-size' => 18,
                    'logdir-size' => 70,
                    'logfile-rotations' => 4,
                ],
            ],
        ]);
        $this->assertEquals(
            $expected,
            $status,
            'Status is not as expected',
        );
    }

    public function testCheckLogRotatingWithErrors(): void
    {
        $file = $this->resourceDir . '/logging.log';
        $handler = $this->createStub(StreamHandler::class);
        $handler->method('getUrl')->willReturn($file);
        $handler->method('getLevel')->willReturn(Level::Warning);
        $logger = $this->createStub(Logger::class);
        $logger->method('getHandlers')->willReturn([$handler]);

        $checker = new MonologChecker('10', '20', 1, $logger);
        $status = $checker->check([]);

        $expected = CheckStatus::createFailure();
        $expected->addReport('logging', [
            'handler' => [
                [
                    'logfile' => $file,
                    'level' => 'WARNING',
                    'logfile-size' => 18,
                    'logdir-size' => 70,
                    'logfile-rotations' => 4,
                ],
            ],
        ]);
        $expected->addMessages(
            'logging',
            [
                'logfile size exceeds 10 bytes',
                'logdir size exceeds 20 bytes',
                'logfile rotations exceed 1',
            ],
        );
        $this->assertEquals(
            $expected,
            $status,
            'Status is not as expected',
        );
    }
}
