<?php

declare(strict_types=1);

namespace Atoolo\EventsCalendar\Dto\Scheduling;

use DateTime;

class SchedulingBuilder
{
    private DateTime $start;

    private ?DateTime $end = null;

    private bool $isFullDay = false;

    private bool $hasStartTime = true;

    private bool $hasEndTime = true;

    private ?string $rRule = null;

    public function build(): Scheduling
    {
        return new Scheduling(
            $this->start,
            $this->end,
            $this->isFullDay,
            $this->hasStartTime,
            $this->hasEndTime,
            $this->rRule,
        );
    }

    public function fromScheduling(Scheduling $scheduling): self
    {
        $this->start = clone $scheduling->start;
        $this->end = $scheduling->end !== null ? clone $scheduling->end : null;
        $this->isFullDay = $scheduling->isFullDay;
        $this->hasStartTime = $scheduling->hasStartTime;
        $this->hasEndTime = $scheduling->hasEndTime;
        $this->rRule = $scheduling->rRule;
        return $this;
    }

    /**
     * @param bool $keepRelativeEnd if true, the end date is changed such that the
     * previous time difference between start and end is preserved. If false, the end
     * date remains unchanged
     */
    public function setStart(DateTime $start, bool $keepRelativeEnd = false): self
    {
        if ($this->end !== null && $keepRelativeEnd) {
            $prevStartEndDiff =  $this->end->getTimestamp() - $this->start->getTimestamp();
            $this->setEnd(
                (clone $start)->setTimestamp($start->getTimestamp() + $prevStartEndDiff),
            );
        }
        $this->start = $start;
        return $this;
    }

    public function setEnd(?DateTime $end): self
    {
        $this->end = $end;
        return $this;
    }

    public function setIsFullDay(bool $isFullDay): self
    {
        $this->isFullDay = $isFullDay;
        return $this;
    }

    public function setHasStartTime(bool $hasStartTime): self
    {
        $this->hasStartTime = $hasStartTime;
        return $this;
    }

    public function setHasEndTime(bool $hasEndTime): self
    {
        $this->hasEndTime = $hasEndTime;
        return $this;
    }

    public function setRRule(?string $rRule): self
    {
        $this->rRule = $rRule;
        return $this;
    }
}
