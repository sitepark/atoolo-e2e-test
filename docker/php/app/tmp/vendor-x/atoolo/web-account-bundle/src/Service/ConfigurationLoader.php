<?php

declare(strict_types=1);

namespace Atoolo\WebAccount\Service;

use Atoolo\Resource\ResourceChannel;
use Atoolo\WebAccount\Dto\Config\WebAccountConfiguration;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class ConfigurationLoader
{
    public function __construct(
        #[Autowire(service: 'atoolo_resource.resource_channel')]
        private readonly ResourceChannel $resourceChannel,
        private readonly DenormalizerInterface $serializer,
    ) {}

    public function load(string $name): WebAccountConfiguration
    {
        $file = $this->getFile($name);

        if (!file_exists($file)) {
            throw new InvalidArgumentException("Configuration '$name' not found.");
        }

        $saveErrorReporting = error_reporting();

        try {
            error_reporting(E_ERROR | E_PARSE);
            ob_start();
            $data = require $file;
            if (!is_array($data)) {
                throw new RuntimeException(
                    'The web-account configuration ' .
                    $file . ' should return an array',
                );
            }

            /** @var WebAccountConfiguration $configuration */
            $configuration = $this->serializer->denormalize($data, WebAccountConfiguration::class, 'json');
            return $configuration;

        } catch (ExceptionInterface $e) {
            throw new InvalidArgumentException(
                'Failed to deserialize web-account configuration ' .
                $file . ': ' . $e->getMessage(),
                0,
                $e,
            );
        } finally {
            ob_end_clean();
            error_reporting($saveErrorReporting);
        }
    }

    private function getFile(string $name): string
    {
        $filename = $this->getSanitizedFileName($name);
        return $this->resourceChannel->configDir .
            '/web-account/' . $filename . '.php';
    }

    private function getSanitizedFileName(string $name): string
    {
        $name = preg_replace('/[^a-zA-Z0-9_\-\s]/', '', $name) ?? '';
        $name = (string) str_replace(' ', '-', $name);
        return substr($name, 0, 255);
    }
}
