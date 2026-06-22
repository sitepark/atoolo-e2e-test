<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Check\Service\Checker;

use Atoolo\Runtime\Check\Service\CheckStatus;
use Monolog\Handler\FingersCrossedHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use SplFileInfo;

/**
 * @phpstan-type ReportData = array{
 *     logfile: ?string,
 *     level: string,
 *     logfile-size?: int,
 *     logdir-size?: int,
 *     logfile-rotations?: int
 * }
 */
class MonologChecker implements Checker
{
    public function __construct(
        private readonly string $maxLogFileSize,
        private readonly string $maxLogDirSize,
        private readonly int $maxLogFileRotations,
        private readonly LoggerInterface $logger,
    ) {}

    public function getScope(): string
    {
        return 'logging';
    }

    public function check(): CheckStatus
    {
        if (!($this->logger instanceof Logger)) {
            $status = CheckStatus::createFailure();
            $status->addMessage(
                $this->getScope(),
                'unknown: unsupported logger ' . get_class($this->logger),
            );
            return $status;
        }

        $handlers = $this->getStreamHandlers($this->logger);
        if (empty($handlers)) {
            $status = CheckStatus::createFailure();
            $status->addMessage(
                $this->getScope(),
                'unknown: no stream handler found',
            );
            return $status;
        }

        $status = CheckStatus::createSuccess();
        foreach ($handlers as $handler) {
            $status = $this->checkHandler($status, $handler);
        }
        return $status;
    }

    private function checkHandler(
        CheckStatus $mergedStatus,
        StreamHandler $handler,
    ): CheckStatus {

        $reportData = $this->getReportData($handler);
        $file = $handler->getUrl();

        $errors = [];
        $logfileError = $this->checkLogfile($file);
        if ($logfileError !== null) {
            $errors[] = $logfileError;
        }
        $rotatingErrors = $this->checkLogRotating($reportData);
        $errors = array_merge($errors, $rotatingErrors);

        if (!empty($errors)) {
            return $this->createFailure(
                $mergedStatus,
                $reportData,
                $errors,
            );
        }

        return $this->createSuccess(
            $mergedStatus,
            $reportData,
        );
    }

    /**
     * @return ReportData
     */
    private function getReportData(StreamHandler $handler): array
    {
        $file = $handler->getUrl();
        $reportData = [
            'logfile' => $file,
            'level' => $handler->getLevel()->getName(),
        ];

        if ($file === null || !file_exists($file) || !is_readable($file)) {
            return $reportData;
        }

        $dir = dirname($file);

        $fileSize = filesize($file) ?: 0;
        $reportData['logfile-size'] = $fileSize;

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir),
        );

        $dirSize = 0;
        /** @var SplFileInfo $fileInfo */
        foreach ($iterator as $fileInfo) {
            if ($fileInfo->isDir()) {
                continue;
            }
            $dirSize += $fileInfo->getSize() ?: 0;
        }
        $reportData['logdir-size'] = $dirSize;

        $rotations =
            count(glob($file . '.*.gz') ?: [])
            + count(glob($file . '.[0-9]') ?: []);
        $reportData['logfile-rotations'] = $rotations;

        return $reportData;
    }

    private function checkLogfile(?string $file): ?string
    {
        if ($file === null) {
            return'logfile not set';
        }

        if (!file_exists($file)) {
            $dir = dirname($file);
            if (!is_dir($dir)) {
                if (!@mkdir($dir, 0777, true) && !is_dir($dir)) {
                    return 'log directory cannot be created: ' . $dir;
                }
            }

            if (!@touch($file)) {
                return 'logfile cannot be created: ' . $file;
            }
        }

        if (!is_writable($file)) {
            return 'logfile not writable: ' . $file;
        }

        return null;
    }

    /**
     * @param ReportData $reportData
     * @return array<string>
     */
    private function checkLogRotating(array $reportData): array
    {
        $errors = [];

        $maxLogFileSize = $this->memoryStringToInteger($this->maxLogFileSize);
        if (
            $maxLogFileSize > 0
            && ($reportData['logfile-size'] ?? 0) > $maxLogFileSize
        ) {
            $errors[] = 'logfile size exceeds '
                . $this->maxLogFileSize . ' bytes';
        }

        $maxLogDirSize = $this->memoryStringToInteger($this->maxLogDirSize);
        if (
            $maxLogDirSize > 0
            && ($reportData['logdir-size'] ?? 0) > $maxLogDirSize
        ) {
            $errors[] = 'logdir size exceeds '
                . $this->maxLogDirSize . ' bytes';
        }
        if (
            $this->maxLogFileRotations > 0
            && ($reportData['logfile-rotations'] ?? 0)
                > $this->maxLogFileRotations
        ) {
            $errors[] = 'logfile rotations exceed '
                . $this->maxLogFileRotations;
        }

        return $errors;
    }

    /**
     * @param ReportData $reportData
     * @param array<string> $messages
     */
    private function createFailure(
        CheckStatus $mergedStatus,
        array $reportData,
        array $messages,
    ): CheckStatus {
        $status = $mergedStatus->apply(CheckStatus::createFailure());
        $status->addMessages($this->getScope(), $messages);
        return $this->applyStatusReport($status, $reportData);
    }

    /**
     * @param ReportData $reportData
     */
    private function createSuccess(
        CheckStatus $mergedStatus,
        array $reportData,
    ): CheckStatus {
        $status = $mergedStatus->apply(CheckStatus::createSuccess());
        return $this->applyStatusReport($status, $reportData);
    }

    /**
     * @param ReportData $reportData
     */
    private function applyStatusReport(
        CheckStatus $status,
        array $reportData,
    ): CheckStatus {
        /**
         * @var array{
         *   handler: ReportData
         * } $report
         */
        $report = $status->getReport($this->getScope());
        $report['handler'][] = $reportData;
        $status->replaceReport($this->getScope(), $report);
        return $status;
    }

    /**
     * @param Logger $logger
     * @return array<StreamHandler>
     */
    private function getStreamHandlers(
        Logger $logger,
    ): array {
        $handlers = [];
        foreach ($logger->getHandlers() as $handler) {
            if ($handler instanceof FingersCrossedHandler) {
                $handler = $handler->getHandler();
            }
            if ($handler instanceof StreamHandler) {
                $handlers[] = $handler;
            }
        }
        return $handlers;
    }

    private function memoryStringToInteger(string $memory): int
    {
        [$number, $suffix] = sscanf($memory, '%u%c') ?? [null, null];
        if (!is_string($suffix)) {
            return (int) $memory;
        }

        $pos = stripos(' KMG', $suffix);
        if (!is_int($pos) || !is_int($number)) {
            return 0;
        }
        return $number * (1024 ** $pos);
    }
}
