<?php

declare(strict_types=1);

namespace Atoolo\Deployment\Console\Command;

use Atoolo\Deployment\Service\Deployer;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'deployment:deploy',
    description: 'Executes all Deployer-Task to deploy ' .
        'the application to be production ready',
    aliases: ['feds:warm-up'],
)]
final class DeployCommand extends Command
{
    public function __construct(
        private readonly Deployer $deployer,
    ) {
        parent::__construct();
    }

    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {
        $successful = $this->deployer->execute();
        return $successful ? Command::SUCCESS : Command::FAILURE;
    }
}
