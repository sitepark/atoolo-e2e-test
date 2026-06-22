<?php

declare(strict_types=1);

namespace Atoolo\Resource\Loader;

use Atoolo\Resource\DataBag;
use Atoolo\Resource\Exception\InvalidResourceException;
use Atoolo\Resource\Exception\ResourceNotFoundException;
use Atoolo\Resource\Loader\SiteKit\ContextStub;
use Atoolo\Resource\Loader\SiteKit\LifecylceStub;
use Atoolo\Resource\Resource;
use Atoolo\Resource\ResourceChannel;
use Atoolo\Resource\ResourceLanguage;
use Atoolo\Resource\ResourceLoader;
use Atoolo\Resource\ResourceLocation;
use Error;
use Locale;
use ParseError;
use Symfony\Component\DependencyInjection\Attribute\AsAlias;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * ResourceLoader that loads resources created with SiteKit aggregators.
 * @phpstan-type ResourceData array{
 *     id: int,
 *     name: string,
 *     objectType: string,
 *     locale: string
 * }
 */
#[AsAlias(id: 'atoolo_resource.resource_loader')]
class SiteKitLoader implements ResourceLoader
{
    /**
     * @var ?array<string, string> $langLocaleMap
     */
    private ?array $langLocaleMap = null;

    public function __construct(
        #[Autowire(service: 'atoolo_resource.resource_channel')]
        private readonly ResourceChannel $resourceChannel,
    ) {}

    /**
     * @throws InvalidResourceException
     * @throws ResourceNotFoundException
     */
    public function load(ResourceLocation $location): Resource
    {
        $data = $this->loadRaw($location);

        $data = $this->validateData($location, $data);

        $resourceLang = ResourceLanguage::of($data['locale']);

        return new Resource(
            $location->location,
            (string) $data['id'],
            $data['name'],
            $data['objectType'],
            $resourceLang,
            new DataBag($data),
        );
    }

    public function exists(ResourceLocation $location): bool
    {
        return file_exists(
            $this->locationToFile($location),
        );
    }

    public function cleanup(): void
    {
        $this->langLocaleMap = null;
    }

    private function locationToFile(ResourceLocation $location): string
    {
        $file = $this->resourceChannel->resourceDir . '/' .
            $location->location;
        $locale = $this->langToLocale($location->lang);
        if (empty($locale)) {
            return $file;
        }

        $translated = $file . '.translations/' . $locale . '.php';
        if (!file_exists($translated)) {
            return $file;
        }

        return $translated;
    }

    /**
     * @return array<string,mixed>
     * @throws InvalidResourceException
     * @throws ResourceNotFoundException
     */
    private function loadRaw(ResourceLocation $location): array
    {
        $file = $this->locationToFile($location);

        /**
         * $context and $lifecycle must be defined here, because for the SiteKit
         * resource PHP files these variables must be provided for the
         * requirement call.
         */
        $context = new ContextStub();
        $lifecycle = new LifecylceStub();

        $saveErrorReporting = error_reporting();

        try {
            error_reporting(E_ERROR | E_PARSE);
            ob_start();
            $data = require $file;
            if (!is_array($data)) {
                throw new InvalidResourceException(
                    $location,
                    'The resource should return an array',
                );
            }
            /** @var ResourceData $data */
            return $data;
        } catch (ParseError $e) {
            throw new InvalidResourceException(
                $location,
                $e->getMessage(),
                0,
                $e,
            );
        } catch (Error $e) {
            if (!file_exists($file)) {
                throw new ResourceNotFoundException(
                    $location,
                    $e->getMessage(),
                    0,
                    $e,
                );
            }
            throw new InvalidResourceException(
                $location,
                $e->getMessage(),
                0,
                $e,
            );
        } finally {
            ob_end_clean();
            error_reporting($saveErrorReporting);
        }
    }

    private function langToLocale(ResourceLanguage $lang): string
    {
        if ($lang === ResourceLanguage::default()) {
            return '';
        }

        $this->langLocaleMap ??= $this->createLangLocaleMap();

        return $this->langLocaleMap[$lang->code] ?? '';
    }

    /**
     * @return array<string, string>
     */
    private function createLangLocaleMap(): array
    {
        $map = [];
        foreach (
            $this->resourceChannel->translationLocales as $availableLocale
        ) {
            $primaryLang = Locale::getPrimaryLanguage($availableLocale);
            $map[$primaryLang] = $availableLocale;
        }
        return $map;
    }

    /**
     * @param ResourceLocation $location
     * @param array<string,mixed> $data
     * @return ResourceData
     */
    private function validateData(
        ResourceLocation $location,
        array $data,
    ): array {

        if (!isset($data['id'])) {
            throw new InvalidResourceException(
                $location,
                'id field missing',
            );
        }
        if (!is_int($data['id'])) {
            throw new InvalidResourceException(
                $location,
                'id field not an int',
            );
        }
        if (!isset($data['name'])) {
            throw new InvalidResourceException(
                $location,
                'name field missing',
            );
        }
        if (!is_string($data['name'])) {
            throw new InvalidResourceException(
                $location,
                'name field not a string',
            );
        }
        if (!isset($data['objectType'])) {
            throw new InvalidResourceException(
                $location,
                'objectType field missing',
            );
        }
        if (!is_string($data['objectType'])) {
            throw new InvalidResourceException(
                $location,
                'objectType field not a string',
            );
        }
        if (!isset($data['locale'])) {
            throw new InvalidResourceException(
                $location,
                'locale field missing',
            );
        }
        if (!is_string($data['locale'])) {
            throw new InvalidResourceException(
                $location,
                'locale field not a string',
            );
        }

        /** @var ResourceData $data */
        return $data;
    }
}
