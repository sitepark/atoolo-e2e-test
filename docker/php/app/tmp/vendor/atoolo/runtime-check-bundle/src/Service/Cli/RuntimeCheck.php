<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Check\Service\Cli;

use Atoolo\Runtime\Check\Service\Checker\CheckerCollection;
use Atoolo\Runtime\Check\Service\CheckStatus;
use Atoolo\Runtime\Check\Service\RuntimeStatus;
use Atoolo\Runtime\Check\Service\RuntimeType;
use Atoolo\Runtime\Check\Service\Worker\WorkerStatusFile;

class RuntimeCheck
{
    public function __construct(
        private readonly CheckerCollection $checkerCollection,
        private readonly FastCgiStatusFactory $fastCgiStatusFactory,
        private readonly WorkerStatusFile $workerStatusFile,
    ) {}

    /**
     * @param array<string> $skip
     */
    public function execute(array $skip, ?string $fpmSocket): RuntimeStatus
    {
        $runtimeStatus = new RuntimeStatus();
        if (!in_array(RuntimeType::CLI->value, $skip, true)) {
            $runtimeStatus->addStatus(
                RuntimeType::CLI,
                $this->getCliStatus($skip),
            );
        }
        if (!in_array(RuntimeType::FPM_FCGI->value, $skip, true)) {
            $runtimeStatus->addStatus(
                RuntimeType::FPM_FCGI,
                $this->getFpmFcgiStatus($skip, $fpmSocket),
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
    private function getCliStatus(array $skip): CheckStatus
    {
        return $this->checkerCollection->check($skip);
    }

    /**
     * @param array<string> $skip
     */
    private function getFpmFcgiStatus(
        array $skip,
        ?string $fpmSocket,
    ): CheckStatus {
        $fastCgi = $this->fastCgiStatusFactory->create($fpmSocket);
        return $fastCgi->request($skip);
    }

    /**
     * @param array<string> $skip
     */
    private function getWorkerStatus(array $skip): CheckStatus
    {
        return $this->workerStatusFile->read();
    }
}
