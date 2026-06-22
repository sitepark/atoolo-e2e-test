<?php

declare(strict_types=1);

namespace Atoolo\Security\SiteKit;

use Atoolo\Resource\ResourceChannel;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\ChainRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcher\IpsRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcher\PathRequestMatcher;
use Symfony\Component\HttpFoundation\RequestMatcherInterface;
use Symfony\Component\Security\Http\AccessMap;
use Symfony\Component\Security\Http\AccessMapInterface;

class AccessMapFactory
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var AccessMap
     */
    private $accessMap;

    public function __construct(
        #[Autowire(service: 'atoolo_resource.resource_channel')]
        private readonly ResourceChannel $resourceChannel,
        LoggerInterface $logger,
    ) {
        $this->logger  = $logger;
    }

    public function create(): AccessMapInterface
    {
        $this->accessMap = new AccessMap();

        /** @var string $resourceRoot */
        $resourceRoot = $this->resourceChannel->resourceDir;
        $baseDir = $resourceRoot . '/security';

        if (!is_dir($baseDir)) {
            return $this->accessMap;
        }

        $finder = new Finder();
        $finder->in($baseDir);
        foreach ($finder->files()->name('*.access.php') as $file) {
            $this->loadByFile($file->getRealPath());
        }

        return $this->accessMap;
    }

    /**
     */
    private function loadByFile(string $file): void
    {
        try {
            $dataList = $this->loadDataList($file);
            foreach ($dataList as $data) {
                $requestMatcher = $this->createRequestMatcher($data);
                $attributes = RoleMapper::map($data['roles'] ?? []);
                $this->accessMap->add($requestMatcher, $attributes);
            }
        } catch (\Throwable $t) {
            $this->logger->error(
                'unable to load access-map',
                ['file' => $file, 'exception' => $t],
            );
        }
    }

    /**
     * @return array<array{path?: string, ips?: string, roles?: array<string>}>
     */
    private function loadDataList(string $file): array
    {
        // phpcs:ignore
        return @include $file;
    }

    /**
     * @param array{path?: string, ips?: string} $data
     */
    private function createRequestMatcher(array $data): RequestMatcherInterface
    {
        $matchers = [];
        if (isset($data['path'])) {
            $matchers[] = new PathRequestMatcher($data['path']);
        }
        if (isset($data['ips'])) {
            $matchers[] = new IpsRequestMatcher($data['ips']);
        }

        return new ChainRequestMatcher($matchers);
    }
}
