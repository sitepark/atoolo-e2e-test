<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Composer;

use Atoolo\Runtime\AtooloRuntime;
use Composer\Composer;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Filesystem\Filesystem;

class RuntimeFile
{
    private const RUNTIME_FILE = 'vendor/atoolo_runtime.php';

    public function __construct(
        private readonly Composer $composer,
        private readonly string $projectDir,
    ) {}

    public function getRuntimeFilePath(): string
    {
        return self::RUNTIME_FILE;
    }

    /**
     * @throws InvalidArgumentException
     *  if the configured template file does not exist
     */
    public function updateRuntimeFile(IOInterface $io): void
    {
        $vendorDir = $this->getVendorDir();

        $runtimeTemplateFile = $this->getRuntimeTemplateFile();
        $projectDir = $this->getProjectDir();

        $rootRuntimeOptions = $this->getPackageRuntimeOptions(
            $this->composer->getPackage(),
        );
        $runtimeClass = $rootRuntimeOptions['class']
            ?? AtooloRuntime::class;

        $runtimeOptions = $this->getRuntimeOptions();

        $runtimeTemplate = @file_get_contents($runtimeTemplateFile);
        if ($runtimeTemplate === false) {
            throw new RuntimeException(
                'Failed to read runtime template file: '
                . $runtimeTemplateFile,
            );
        }
        $code = strtr($runtimeTemplate, [
            '%project_dir%' => $projectDir,
            '%runtime_class%' => var_export($runtimeClass, true),
            '%runtime_options%' => var_export($runtimeOptions, true),
        ]);

        $path = $vendorDir . '/atoolo_runtime.php';
        $fs = new \Composer\Util\Filesystem();
        if ($fs->filePutContentsIfModified($path, $code) !== 0) {
            $io->write('<info>' . 'Write ' . $path . '</info>');
        }
    }

    public function removeRuntimeFile(IOInterface $io): void
    {
        $vendorDir = $this->getVendorDir();
        $runtimeFile = $vendorDir . '/atoolo_runtime.php';
        if (file_exists($runtimeFile)) {
            $io->write('<info>' . 'Remove ' . $runtimeFile . '</info>');
            unlink($runtimeFile);
        }
    }

    private function getVendorDir(): string
    {
        $vendorDir = $this->composer->getConfig()->get('vendor-dir');
        if (!is_string($vendorDir)) {
            throw new RuntimeException(
                'Unable to determine the vendor directory: '
                . print_r($vendorDir, true),
            );
        }
        $vendorDir = realpath($vendorDir);
        if ($vendorDir === false) {
            throw new RuntimeException(
                'Unable to determine the vendor directory.',
            );
        }

        return $vendorDir;
    }

    /**
     * @return RuntimeOptions
     */
    private function getRuntimeOptions(): array
    {
        $options = [];
        $repo = $this->composer->getRepositoryManager()->getLocalRepository();

        $packages = array_merge(
            $repo->getPackages(),
            [$this->composer->getPackage()],
        );
        foreach ($packages as $package) {
            $packageOptions = $this->getPackageRuntimeOptions($package);
            if (empty($packageOptions)) {
                continue;
            }
            $options[$package->getName()] = $packageOptions;
        }

        return $options;
    }

    /**
     * @throws InvalidArgumentException
     *  if a configured template file does not exist
     */
    private function getRuntimeTemplateFile(): string
    {
        $fs = new Filesystem();

        /** @var RuntimeRootPackageOptions $rootPackageOptions */
        $rootPackageOptions = $this->getPackageRuntimeOptions(
            $this->composer->getPackage(),
        );
        $runtimeTemplateFile = $rootPackageOptions['template'] ?? null;
        if ($runtimeTemplateFile === null) {
            $runtimeTemplateFile = __DIR__ . '/atoolo_runtime.template';
        }
        if (!$fs->isAbsolutePath($runtimeTemplateFile)) {
            $runtimeTemplateFile = $this->projectDir . '/'
                . $runtimeTemplateFile;
        }
        if (!is_file($runtimeTemplateFile)) {
            throw new InvalidArgumentException(
                sprintf(
                    'File "%s" defined under '
                    . '"extra.atoolo.runtime.template"'
                    . ' in your composer.json not found.',
                    $runtimeTemplateFile,
                ),
            );
        }

        return $runtimeTemplateFile;
    }

    /**
     * @return RuntimePackageOptions|RuntimeRootPackageOptions
     */
    private function getPackageRuntimeOptions(PackageInterface $package): array
    {
        /**
         * @var array{
         *     atoolo?: array{
         *         runtime?: RuntimePackageOptions|RuntimeRootPackageOptions
         *     }
         * } $extra
         */
        $extra = $package->getExtra();
        return $extra['atoolo']['runtime'] ?? [];
    }

    private function getProjectDir(): string
    {

        $vendorDir = $this->getVendorDir();

        $fs = new Filesystem();
        $projectDir = $fs->makePathRelative($this->projectDir, $vendorDir);
        $nestingLevel = 0;

        while (str_starts_with($projectDir, '../')) {
            ++$nestingLevel;
            $projectDir = substr($projectDir, 3);
        }

        $dirname = $nestingLevel === 0
            ? '__DIR__.'
            : sprintf('dirname(__DIR__, %d)', $nestingLevel);
        return '' !== $projectDir
            ? $dirname . var_export('/' . $projectDir, true)
            : $dirname;
    }
}
