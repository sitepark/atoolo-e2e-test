<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Check\Service\FpmFcgi;

use Atoolo\Runtime\Check\Service\Checker\CheckerCollection;
use Atoolo\Runtime\Check\Service\CheckStatus;
use Atoolo\Runtime\Check\Service\RuntimeStatus;
use Atoolo\Runtime\Check\Service\RuntimeType;
use Atoolo\Runtime\Check\Service\Worker\WorkerStatusFile;
use JsonException;

class RuntimeCheck
{
    public function __construct(
        private readonly CheckerCollection $checkerCollection,
        private readonly CliStatus $cliStatus,
        private readonly WorkerStatusFile $workerStatusFile,
    ) {}

    /**
     * @param array<string> $skip
     * @throws JsonException
     */
    public function execute(array $skip): RuntimeStatus
    {
        $runtimeStatus = new RuntimeStatus();
        if (!in_array(RuntimeType::FPM_FCGI->value, $skip, true)) {
            $runtimeStatus->addStatus(
                RuntimeType::FPM_FCGI,
                $this->getFpmFcgiStatus($skip),
            );
        }
        if (!in_array(RuntimeType::CLI->value, $skip, true)) {
            $runtimeStatus->addStatus(
                RuntimeType::CLI,
                $this->getCliStatus($skip),
            );
        }
        if (!in_array(RuntimeType::WORKER->value, $skip, true)) {
            $runtimeStatus->addStatus(
                RuntimeType::WORKER,
                $this->getWorkerStatus($skip),
            );
        }

        return $runtimeStatus;
    }

    /**
     * @param array<string> $skip
     */
    private function getFpmFcgiStatus(
        array $skip,
    ): CheckStatus {
        $status = $this->checkerCollection->check($skip);
        $status->addReport('server', [
            'host' => $_SERVER['SERVER_NAME'] ?? 'unknown',
        ]);
        return $status;
    }

    /**
     * @param array<string> $skip
     * @throws JsonException
     */
    private function getCliStatus(array $skip): CheckStatus
    {
        return $this->cliStatus->execute($skip);
    }

    /**
     * @param array<string> $skip
     * @throws JsonException
     */
    private function getWorkerStatus(array $skip): CheckStatus
    {
        return $this->workerStatusFile->read();
    }
}
