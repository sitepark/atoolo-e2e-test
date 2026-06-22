<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Composer;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Factory;
use Composer\IO\IOInterface;
use Composer\Plugin\PluginInterface;
use Composer\Script\ScriptEvents;
use InvalidArgumentException;
use JsonException;

class ComposerPlugin implements PluginInterface, EventSubscriberInterface
{
    private IOInterface $io;

    private RuntimeFile $runtimeFile;

    private ComposerJson $composerJson;

    private ComposerJsonFactory $composerJsonFactory;

    private RuntimeFileFactory $runtimeFileFactory;

    private static bool $activated = false;

    public function __construct(
        ?ComposerJsonFactory $composerJsonFactory = null,
        ?RuntimeFileFactory $runtimeFileFactory = null,
    ) {
        $this->composerJsonFactory = $composerJsonFactory
            ?? new ComposerJsonFactory();
        $this->runtimeFileFactory = $runtimeFileFactory
            ?? new RuntimeFileFactory();
    }

    /**
     * @return array<string,array<string|int>>
     */
    public static function getSubscribedEvents(): array
    {
        if (!self::$activated) {
            return [];
        }

        $priority = 1;
        return [
            ScriptEvents::POST_INSTALL_CMD => ['generateRuntime', $priority],
            ScriptEvents::PRE_AUTOLOAD_DUMP => ['updateRuntime', $priority],
        ];
    }

    public function activate(Composer $composer, IOInterface $io): void
    {
        $this->io = $io;
        $this->composerJson = $this->composerJsonFactory->create(
            $composer,
            Factory::getComposerFile(),
        );
        $this->runtimeFile = $this->runtimeFileFactory->create(
            $composer,
            dirname($this->composerJson->getPath()),
        );
        self::$activated = true;
    }

    /**
     * @throws InvalidArgumentException
     *  if the configured template file does not exist
     */
    public function updateRuntime(): void
    {
        $this->runtimeFile->updateRuntimeFile($this->io);
    }

    /**
     * @throws JsonException
     * @throws InvalidArgumentException
     *  if the configured template file does not exist
     */
    public function generateRuntime(): void
    {
        $this->updateRuntime();
        $this->composerJson->addAutoloadFile(
            $this->runtimeFile->getRuntimeFilePath(),
        );
    }

    public function deactivate(Composer $composer, IOInterface $io): void
    {
        self::$activated = false;
    }

    public function uninstall(Composer $composer, IOInterface $io): void
    {
        $this->runtimeFile->removeRuntimeFile($this->io);
        $this->composerJson->removeAutoloadFile(
            $this->runtimeFile->getRuntimeFilePath(),
        );
    }
}
