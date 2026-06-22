<?php

declare(strict_types=1);

namespace Atoolo\EventsCalendar\Test\Constraint;

use PHPUnit\Framework\Constraint\Callback;
use PHPUnit\Framework\Constraint\Constraint;
use PHPUnit\Framework\Constraint\RegularExpression;

/**
 * Prüft rudimentär ob ein String eine gültige RRule ist.
 */
class IsRRule extends Constraint
{
    private array $failureCauses = [];

    private const FREQ = [
        "SECONDLY",
        "MINUTELY",
        "HOURLY",
        "DAILY",
        "WEEKLY",
        "MONTHLY",
        "YEARLY",
    ];

    private ?array $rruleParts = null;

    public function matches($rruleString): bool
    {
        if (!is_string($rruleString)) {
            $this->failureCauses[] = 'RRule is not a string';
            return false;
        }
        $rruleDefParts = $this->getRRuleDefinitionParts();
        $parts = explode(';', $rruleString);
        $rruleKeysSeen = [];
        foreach ($parts as $part) {
            $partPair = explode('=', $part);
            if (count($partPair) !== 2) {
                $this->failureCauses[] = 'Invalid format. Maybe you missed a = or a ;';
                return false;
            }
            $constraint = $rruleDefParts[$partPair[0]] ?? null;
            if ($constraint === null) {
                $this->failureCauses[] = 'Unknown keyword \'' . $partPair[0] . '\'';
                return false;
            }
            if (isset($rruleKeysSeen[$partPair[0]])) {
                $this->failureCauses[] = 'Duplicate keyword \'' . $partPair[0] . '\'';
                return false;
            }
            if (
                ($partPair[0] === 'UNTIL' && isset($rruleKeysSeen['COUNT']))
                || ($partPair[0] === 'COUNT' && isset($rruleKeysSeen['UNTIL']))
            ) {
                $this->failureCauses[] = 'Keywords \'COUNT\' and \'UNTIL\' are not allowed in the same rrule';
                return false;
            }
            $rruleKeysSeen[$partPair[0]] = true;
            if ($constraint !== false && !$constraint->matches($partPair[1])) {
                $this->failureCauses[] = $partPair[1] . ' is not a valid value for field \'' . $partPair[0] . '\'';
                return false;
            }
        }
        if (!isset($rruleKeysSeen['FREQ'])) {
            $this->failureCauses[] = 'Required field \'FREQ\' missing';
            return false;
        }
        return true;
    }

    public function toString(): string
    {
        return 'is a valid RRule';
    }

    private function getRRuleDefinitionParts(): array
    {
        if ($this->rruleParts === null) {
            $this->rruleParts = [
                'FREQ' => new Callback(fn($v) => in_array($v, self::FREQ)),
                'UNTIL' => new RegularExpression('/^\d{8,}T\d{6}Z$/'),
                'COUNT' => new Callback(fn($v) => ctype_digit($v)),
                'INTERVAL' => new Callback(fn($v) => ctype_digit($v)),
                'BYSECOND' => false,
                'BYMINUTE' => false,
                'BYHOUR' => false,
                'BYDAY' => false,
                'BYMONTHDAY' => false,
                'BYYEARDAY' => false,
                'BYWEEKNO' => false,
                'BYMONTH' => false,
                'BYSETPOS' => false,
                'WKST' => false,
            ];
        }
        return $this->rruleParts;
    }

    protected function additionalFailureDescription(mixed $other): string
    {
        return implode("\n", $this->failureCauses);
    }
}
