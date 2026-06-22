<?php

namespace Atoolo\EventsCalendar\Service\ICal\Eluceo;

use Eluceo\iCal\Presentation\Component\Property\Value;

/**
 * Extension for the eluceo/ical lib
 *
 * @codeCoverageIgnore
 */
class RRuleValue extends Value
{
    private string $rRule;

    public function __construct(string $rRule)
    {
        $this->rRule = $rRule;
    }

    public function __toString(): string
    {
        return $this->rRule;
    }
}
