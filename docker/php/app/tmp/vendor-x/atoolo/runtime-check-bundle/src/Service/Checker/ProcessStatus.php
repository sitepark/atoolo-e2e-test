<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Check\Service\Checker;

use Atoolo\Runtime\Check\Service\CheckStatus;
use Atoolo\Runtime\Check\Service\Platform;

class ProcessStatus implements Checker
{
    /**
     * @throws \JsonException
     */
    public function __construct(
        private readonly Platform $platform = new Platform(),
    ) {}


    public function getScope(): string
    {
        return 'process';
    }

    public function check(): CheckStatus
    {

        $status = CheckStatus::createSuccess();
        $status->addReport($this->getScope(), [
            'script' => $_SERVER['SCRIPT_FILENAME'] ?? 'n/a',
            'user' => $this->platform->getUser(),
            'group' => $this->platform->getGroup(),
        ]);

        return $status;
    }
}
