<?php

declare(strict_types=1);

namespace Atoolo\Deployment\Service;

use Psr\Log\LoggerInterface;

class Deployer
{
    /**
     * @param iterable|DeploymentExecutable[] $deploymentTasks
     */
    public function __construct(
        private readonly iterable $deploymentTasks,
        private readonly LoggerInterface $logger,
    ) {}

    public function execute(): bool
    {

        $successful = true;
        foreach ($this->deploymentTasks as $task) {
            try {
                $task->execute();
            } catch (\Throwable $throwable) {
                $successful = false;
                $this->logger->warning(
                    'An exception occurred during deployment task: ' .
                        $throwable->getMessage(),
                    ['exception' => $throwable, 'task' => get_class($task)],
                );
            }
        }

        return $successful;
    }
}
