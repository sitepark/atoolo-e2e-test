<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Check\Service\Worker;

use Atoolo\Runtime\Check\Service\CheckStatus;
use Atoolo\Runtime\Check\Service\Platform;
use DateTime;
use JsonException;
use RuntimeException;

class WorkerStatusFile
{
    public function __construct(
        private readonly string $workerStatusFile,
        public readonly int $updatePeriodInMinutes,
        private readonly Platform $platform = new Platform(),
    ) {}

    /**
     * @throws JsonException
     */
    public function read(): CheckStatus
    {
        if (!file_exists($this->workerStatusFile)) {
            return CheckStatus::createFailure()
                ->addMessage('worker', 'worker not running');
        }

        $toleranceInMinutes = $this->updatePeriodInMinutes / 2;

        $workerStatusFileContent = @file_get_contents($this->workerStatusFile);
        if ($workerStatusFileContent === false) {
            throw new RuntimeException(
                'Unable to read file ' . $this->workerStatusFile,
            );
        }

        /** @var CheckStatusData $result */
        $result = json_decode(
            $workerStatusFileContent,
            true,
            512,
            JSON_THROW_ON_ERROR,
        );

        $allowedTime =
            $this->platform->time() -
            (($this->updatePeriodInMinutes + $toleranceInMinutes) * 60);
        $formattedLastRun = $result['reports']['scheduler']['last-run']
            ?? 'unknown';
        $lastRun = is_string($formattedLastRun)
            ? strtotime($formattedLastRun)
            : 0;

        $checkStatus = CheckStatus::deserialize($result);
        if ($lastRun < $allowedTime) {
            $checkStatus->success = false;
            if (isset($result['reports']['scheduler'])) {
                $checkStatus
                    ->replaceReport(
                        'scheduler',
                        $result['reports']['scheduler'],
                    );
            }
            $checkStatus
                ->addMessage(
                    'scheduler',
                    'The worker did not run in the last '
                    . $this->updatePeriodInMinutes
                    . ' minutes. Last run: '
                    . $formattedLastRun,
                );
        }

        return $checkStatus;
    }

    /**
     * @throws JsonException
     */
    public function write(CheckStatus $status): void
    {
        $now = new DateTime();
        $now->setTimestamp($this->platform->time()); // testable
        if (isset($_SERVER['SUPERVISOR_ENABLED'])) {
            $status->addReport('supervisor', [
                'group' => $_SERVER['SUPERVISOR_GROUP_NAME'] ?? '',
                'process' => $_SERVER['SUPERVISOR_PROCESS_NAME'] ?? '',
            ]);
        }
        $status->addReport('scheduler', [
            'last-run' => $now->format('d.m.Y H:i:s'),
        ]);

        file_put_contents(
            $this->workerStatusFile,
            json_encode(
                $status->serialize(),
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
            ),
        );
    }
}
