<?php

declare(strict_types=1);

namespace Atoolo\EventsCalendar\Test\Constraint;

use PHPUnit\Framework\Constraint\Constraint;

class EqualsRRule extends Constraint
{
    public function __construct(
        private readonly string $expected,
    ) {}

    public function matches($actual): bool
    {
        if (!is_string($actual)) {
            return false;
        }
        $partsActual = explode(';', $actual);
        $partsExpected = explode(';', $this->expected);
        $rruleArrayExpected = [];
        foreach ($partsExpected as $part) {
            $partPair = explode('=', $part);
            $rruleArrayExpected[$partPair[0]] = $partPair[1];
        }
        foreach ($partsActual as $part) {
            $partPair = explode('=', $part);
            if (!isset($rruleArrayExpected[$partPair[0]])) {
                return false;
            }
            if ($partPair[0] === 'BYDAY') {
                $bydayExpected = explode(',', $rruleArrayExpected[$partPair[0]]);
                $bydayActual = explode(',', $partPair[1]);
                if (!empty(array_diff($bydayExpected, $bydayActual))) {
                    return false;
                }
            } elseif ($rruleArrayExpected[$partPair[0]] !== $partPair[1]) {
                return false;
            }
        }

        return true;
    }

    public function toString(): string
    {
        return 'is equal to rrule \'' . $this->expected . '\'';
    }
}
