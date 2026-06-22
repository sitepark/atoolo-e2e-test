<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Check\Service\Worker;

use DateTimeImmutable;
use Symfony\Component\Scheduler\Trigger\TriggerInterface;

class OneTimeTrigger implements TriggerInterface
{
    private ?DateTimeImmutable $run = null;

    /**
     * @inheritDoc
     */
    public function __toString(): string
    {
        if ($this->run === null) {
            return 'one time';
        }
        return 'one time (already running)';
    }

    /**
     * @inheritDoc
     */
    public function getNextRunDate(\DateTimeImmutable $run): ?\DateTimeImmutable
    {
        if (null === $this->run) {
            $this->run = $run;
            return $run;
        }
        return null;
    }
}
