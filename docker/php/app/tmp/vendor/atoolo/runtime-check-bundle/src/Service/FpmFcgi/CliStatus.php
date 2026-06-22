<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Check\Service\FpmFcgi;

use Atoolo\Runtime\Check\Service\CheckStatus;
use Atoolo\Runtime\Check\Service\RuntimeStatus;
use Atoolo\Runtime\Check\Service\RuntimeType;
use Exception;
use JsonException;
use Symfony\Component\Process\Process;

class CliStatus
{
    public function __construct(
        private readonly string $consoleBinPath,
        private readonly string $resourceRoot,
    ) {}

    /**
     * @param array<string> $skip
     * @throws JsonException
     */
    public function execute(array $skip): CheckStatus
    {
        foreach (RuntimeType::otherCases(RuntimeType::CLI) as $type) {
            $skip[] = $type->value;
        }

        $process = new Process([
            $this->consoleBinPath,
            'runtime:check',
            '--fail-on-error', 'false',
            '--skip', implode(',', $skip),
            '--json',
        ]);
        $process->setEnv(['RESOURCE_ROOT' => $this->resourceRoot]);
        try {
            $process->run();
            // @codeCoverageIgnoreStart
            // found no way to test this
        } catch (Exception $e) {
            return CheckStatus::createFailure()
                ->addMessage('cli', trim($e->getMessage()));
        }
        // @codeCoverageIgnoreEnd

        if (!$process->isSuccessful()) {
            return CheckStatus::createFailure()
                ->addMessage('cli', trim($process->getErrorOutput()));
        }

        /** @var RuntimeStatusData $data */
        $data = json_decode(
            $process->getOutput(),
            true,
            512,
            JSON_THROW_ON_ERROR,
        );
        $status = RuntimeStatus::deserialize($data)
            ->getStatus(RuntimeType::CLI);
        if ($status === null) {
            return CheckStatus::createFailure()
                ->addMessage('cli', 'No CLI status found in response.');
        }
        return $status;
    }
}
