<?php

declare(strict_types=1);

namespace Atoolo\EventsCalendar\Service\ICal\Eluceo;

use Eluceo\iCal\Domain\Entity\Event;
use Eluceo\iCal\Presentation\Component;
use Eluceo\iCal\Presentation\Component\Property;
use Eluceo\iCal\Presentation\Component\Property\Value\TextValue;

/**
 * Extension for the eluceo/ical lib
 *
 * Customized iCal event factory to support attributes like RRULE or RELATED-TO
 *
 * @codeCoverageIgnore
 */
class CustomEventFactory extends \Eluceo\iCal\Presentation\Factory\EventFactory
{
    public function createComponent(Event $event): Component
    {
        $component = parent::createComponent($event);
        if ($event instanceof CustomEvent) {
            if ($event->hasRRule()) {
                $component = $component->withProperty(
                    new Property(
                        'RRULE',
                        new RRuleValue($event->getRRule()),
                    ),
                );
            }
            if ($event->hasRelatedTo()) {
                $component = $component->withProperty(
                    new Property(
                        'RELATED-TO',
                        new TextValue((string) $event->getRelatedTo()),
                    ),
                );
            }
        }
        return $component;
    }
}
