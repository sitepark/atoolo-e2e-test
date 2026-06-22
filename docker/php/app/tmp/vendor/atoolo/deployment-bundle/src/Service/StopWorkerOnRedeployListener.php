<?php

declare(strict_types=1);

namespace Atoolo\Deployment\Service;

use Psr\Log\LoggerInterface;
use RuntimeException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Messenger\Event\WorkerRunningEvent;
use Symfony\Component\Messenger\Event\WorkerStartedEvent;

/**
 * If the application is redeployed, this is done by setting a symlink to
 * the newly installed version. The old project directory is still retained
 * to enable a rollback. After redeploying the application, all running
 * Massenger workers are invalid and must be stopped.
 *
 * To recognize this, a file is stored in the cache directory of the project
 * in which a random hash value is saved. The `WorkerRunningEvent` is used to
 * check whether the file still exists in the project cache directory and
 * whether it contains the expected hash value. If this is not the case, the
 * project directory has changed and the worker is stopped.
 *
 * The worker is restarted via a process manager such as
 * [Supervisor](http://supervisord.org/), which monitors the process and also
 * restarts it if the process has been stopped. The worker process is
 * then restarted for the newly deployed project.
 */
class StopWorkerOnRedeployListener implements EventSubscriberInterface
{
    private string $scriptFilename;
    private string $scriptFilenameRealPath;

    public function __construct(
        private readonly LoggerInterface $workerLogger,
    ) {
        if (!isset($_SERVER['SCRIPT_FILENAME'])) {
            throw new RuntimeException(
                '$_SERVER[\'SCRIPT_FILENAME\'] is not set',
            );
        }
        $this->scriptFilename = $_SERVER['SCRIPT_FILENAME'];
        $scriptFilenameRealPath = realpath($this->scriptFilename);
        if ($scriptFilenameRealPath === false) {
            throw new RuntimeException(
                'unable to determine realpath of ' . $this->scriptFilename,
            );
        }
        $this->scriptFilenameRealPath = $scriptFilenameRealPath;
    }

    public function onWorkerStarted(): void
    {
        $this->workerLogger->info(
            'start redeploy listener with '
            . $this->scriptFilename
            . ' -> '
            . $this->scriptFilenameRealPath,
        );
    }

    public function onWorkerRunning(WorkerRunningEvent $event): void
    {
        if (!$event->isWorkerIdle()) {
            return;
        }
        if (!$this->shouldStop()) {
            return;
        }

        $this->workerLogger->info(
            'The project directory has changed. This project was undeployed.',
        );
        $this->workerLogger->info(
            'The worker is stopped.',
        );
        $event->getWorker()->stop();
    }

    private function shouldStop(): bool
    {
        clearstatcache(true);
        $scriptFilenameRealPath = realpath($this->scriptFilename);
        if ($scriptFilenameRealPath === false) {
            return true;
        }

        return $scriptFilenameRealPath !== $this->scriptFilenameRealPath;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            WorkerStartedEvent::class => 'onWorkerStarted',
            WorkerRunningEvent::class => 'onWorkerRunning',
        ];
    }
}
