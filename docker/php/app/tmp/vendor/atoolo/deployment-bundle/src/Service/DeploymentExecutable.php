<?php

declare(strict_types=1);

namespace Atoolo\Deployment\Service;

interface DeploymentExecutable
{
    public function execute(): void;
}
