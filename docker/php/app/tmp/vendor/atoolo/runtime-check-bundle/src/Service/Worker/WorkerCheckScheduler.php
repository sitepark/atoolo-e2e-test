<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Check\Service\Worker;

use Atoolo\Runtime\Check\Service\Checker\CheckerCollection;
use JsonException;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\Store\SemaphoreStore;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

#[AsSchedule(name: 'atoolo-runtime-check')]
#[AsMessageHandler]
class WorkerCheckScheduler implements ScheduleProviderInterface
{
    private ?Schedule $schedule = null;

    public function __construct(
        private readonly WorkerStatusFile $workerStatusFile,
        private readonly CheckerCollection $checkerCollection,
        private readonly string $host,
        private readonly LockFactory $lockFactory = new LockFactory(
            new SemaphoreStore(),
        ),
    ) {}

    public function getSchedule(): Schedule
    {
        return $this->schedule ??= (new Schedule())
            ->add(
                RecurringMessage::trigger(
                    new OneTimeTrigger(),
                    new WorkerCheckEvent(),
                ),
                RecurringMessage::every(
                    $this->workerStatusFile->updatePeriodInMinutes . ' minutes',
                    new WorkerCheckEvent(),
                ),
            )->lock($this->lockFactory->createLock(
                'runtime-check-scheduler-' . $this->host,
            ));
    }

    /**
     * @throws JsonException
     */
    public function __invoke(WorkerCheckEvent $message): void
    {
        $status = $this->checkerCollection->check([]);
        $this->workerStatusFile->write($status);
    }
}
