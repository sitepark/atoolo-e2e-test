<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Check\Console\Command;

use Atoolo\Runtime\Check\Console\Command\Io\TypifiedInput;
use Atoolo\Runtime\Check\Service\Cli\RuntimeCheck;
use Atoolo\Runtime\Check\Service\RuntimeStatus;
use Atoolo\Runtime\Check\Service\RuntimeType;
use JsonException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'runtime:check',
    description: 'Check the runtime environment',
)]
final class CheckCommand extends Command
{
    public function __construct(
        private readonly RuntimeCheck $runtimeCheck,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $runtimeTypes = implode(
            ', ',
            array_map(
                fn(RuntimeType $type) => $type->value,
                RuntimeType::cases(),
            ),
        );
        $this
            ->setHelp('Command to performs a check of the runtime environment')
            ->addOption(
                'skip',
                null,
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Skip check for different runtime-types'
                . ' (' . $runtimeTypes . ')'
                . ' and scopes (e.g. php, logging, ...)',
                [],
            )
            ->addOption(
                'fpm-socket',
                null,
                InputOption::VALUE_REQUIRED,
                'fpm FastCGI socket like 127.0.0.1:9000 or '
                . '/var/run/php/php8.3-fpm.sock',
            )
            ->addOption(
                'fail-on-error',
                null,
                InputOption::VALUE_OPTIONAL,
                'returns the exit code 1 if an error occurs',
                true,
            )
            ->addOption(
                'json',
                null,
                InputOption::VALUE_NEGATABLE,
                'output result in json.',
                false,
            )
        ;
    }

    /**
     * @throws JsonException
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output,
    ): int {

        $typedInput = new TypifiedInput($input);

        $runtimeStatus = $this->runtimeCheck->execute(
            $typedInput->getArrayOption('skip'),
            $typedInput->getStringOption('fpm-socket'),
        );

        $this->outputResults(
            $output,
            $runtimeStatus,
            $typedInput->getBoolOption('json'),
        );

        $failOnError = $typedInput->getBoolOption('fail-on-error');
        return $runtimeStatus->isSuccess() || !$failOnError
                ? Command::SUCCESS
                : Command::FAILURE;
    }

    /**
     * @throws JsonException
     */
    private function outputResults(
        OutputInterface $output,
        RuntimeStatus $runtimeStatus,
        bool $json,
    ): void {
        if ($json) {
            $output->writeln(
                json_encode(
                    $runtimeStatus->serialize(),
                    JSON_THROW_ON_ERROR
                    | JSON_PRETTY_PRINT
                    | JSON_UNESCAPED_SLASHES,
                ),
            );
        } else {
            foreach (RuntimeType::cases() as $type) {
                $status = $runtimeStatus->getStatus($type);
                if ($status === null) {
                    continue;
                }
                foreach ($status->getReports() as $scope => $value) {
                    $value = json_encode(
                        $value,
                        JSON_THROW_ON_ERROR
                        | JSON_PRETTY_PRINT
                        | JSON_UNESCAPED_SLASHES,
                    );
                    $output->writeln(
                        '<info>'
                        . $type->value
                        . '/'
                        . $scope
                        . '</info>',
                    );
                    $output->writeln($value);
                    $output->writeln('');
                }
            }

            if ($runtimeStatus->isSuccess()) {
                $output->writeln('<info>Success</info>');
            } else {
                $output->writeln('<error>Failure</error>');
                foreach ($runtimeStatus->getMessages() as $scope => $message) {
                    $output->writeln('<error>' . $message . '</error>');
                }
            }
        }
    }
}
