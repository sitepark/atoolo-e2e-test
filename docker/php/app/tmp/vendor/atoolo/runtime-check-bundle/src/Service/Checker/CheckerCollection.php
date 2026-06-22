<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Check\Service\Checker;

use Atoolo\Runtime\Check\Service\CheckStatus;

class CheckerCollection
{
    /**
     * @param iterable<Checker> $checkers
     */
    public function __construct(
        private readonly iterable $checkers,
    ) {}

    /**
     * @param array<string> $scopesToSkip
     */
    public function check(array $scopesToSkip = []): CheckStatus
    {
        $status = CheckStatus::createSuccess();
        foreach ($this->checkers as $checker) {
            if (in_array($checker->getScope(), $scopesToSkip, true)) {
                continue;
            }
            $status->apply($checker->check());
        }
        return $status;
    }
}
