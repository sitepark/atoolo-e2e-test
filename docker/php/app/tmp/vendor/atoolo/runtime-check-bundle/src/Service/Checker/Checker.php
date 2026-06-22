<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Check\Service\Checker;

use Atoolo\Runtime\Check\Service\CheckStatus;

interface Checker
{
    public function getScope(): string;

    public function check(): CheckStatus;
}
