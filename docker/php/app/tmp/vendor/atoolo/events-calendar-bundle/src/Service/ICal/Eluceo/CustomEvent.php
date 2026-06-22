<?php

declare(strict_types=1);

namespace Atoolo\EventsCalendar\Service\ICal\Eluceo;

use Eluceo\iCal\Domain\ValueObject\UniqueIdentifier;

/**
 * Extension for the eluceo/ical lib
 *
 * Customized iCal event to support attributes like RRULE or RELATED-TO
 *
 * @codeCoverageIgnore
 */
class CustomEvent extends \Eluceo\iCal\Domain\Entity\Event
{
    private ?string $rRule = null;

    private ?UniqueIdentifier $relatedTo = null;

    public function getRRule(): ?string
    {
        return $this->rRule;
    }

    public function setRRule(?string $rRule): void
    {
        $this->rRule = $rRule;
    }

    /**
     * @phpstan-assert-if-true !null $this->getRRule()
     */
    public function hasRRule(): bool
    {
        return $this->rRule !== null;
    }

    public function getRelatedTo(): ?UniqueIdentifier
    {
        return $this->relatedTo;
    }

    public function setRelatedTo(?UniqueIdentifier $relatedTo): void
    {
        $this->relatedTo = $relatedTo;
    }

    public function hasRelatedTo(): bool
    {
        return $this->relatedTo !== null;
    }
}
