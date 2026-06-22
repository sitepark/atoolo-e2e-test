<?php

declare(strict_types=1);

namespace Atoolo\Microsite\Test\Service;

use Atoolo\Microsite\Environment\MicrositeContext;
use Atoolo\Microsite\Service\MountService;
use Atoolo\Microsite\Service\Platform;
use Atoolo\Resource\DataBag;
use Atoolo\Resource\Exception\InvalidResourceException;
use Atoolo\Resource\Exception\ResourceNotFoundException;
use Atoolo\Resource\Resource;
use Atoolo\Resource\ResourceLanguage;
use Atoolo\Resource\ResourceLoader;
use Atoolo\Resource\ResourceLocation;
use Atoolo\Rewrite\Dto\Url;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

#[CoversClass(MountService::class)]
class MountServiceTest extends TestCase
{
    private MicrositeContext $micrositeContext;
    private MountService $mountService;
    private ResourceLoader&Stub $resourceLoader;
    private LoggerInterface&Stub $logger;
    private Platform $platform;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->micrositeContext = new MicrositeContext(
            resourceDir: '',
            currentPath: '/',
            micrositeHost: 'www.test.com',
            micrositePath: '/microsite/abc',
            mainHost: 'www.example.com',
            siteId: 123,
            mountableObjectTypes: ['event'],
        );
        $this->resourceLoader = $this->createStub(ResourceLoader::class);
        $this->platform = $this->createStub(Platform::class);
        $this->logger = $this->createStub(LoggerInterface::class);

        $this->mountService = new MountService(
            $this->micrositeContext,
            $this->resourceLoader,
            $this->platform,
            $this->logger,
        );
    }

    public function testToMountedUrl(): void
    {
        $url = Url::builder()->path('/path')->params(['p' => 'value', 'other' => 'value'])->build();

        $result = $this->mountService->toMountedUrl($url);

        $expected = Url::builder()->path('/path')->params(['p' => 'value', 'other' => 'value'])->build();

        $this->assertEquals($expected, $result);
    }

    public function testIsMountableWithMicrositePath(): void
    {
        $this->assertFalse($this->mountService->isMountable('/microsite/abc/path'));
    }

    public function testIsMountableWithCurrentPathAlreadyMounted(): void
    {
        $this->platform->method('fileExists')->willReturn(true);
        $this->assertFalse($this->mountService->isMountable('/path'));
    }

    public function testIsMountableWithCurrentPathIsNull(): void
    {
        $micrositeContext = new MicrositeContext(
            resourceDir: '',
            currentPath: null,
            micrositeHost: 'www.test.com',
            micrositePath: '/microsite/abc',
            mainHost: 'www.example.com',
            siteId: 123,
            mountableObjectTypes: [],
        );
        $mountService = new MountService(
            $micrositeContext,
            $this->resourceLoader,
            $this->platform,
            $this->logger,
        );

        $resource = $this->createResource('/event', 'event');
        $this->resourceLoader->method('load')->willReturn($resource);

        $this->platform->method('fileExists')->willReturn(true);

        $this->assertFalse($mountService->isMountable('/path'));
    }

    public function testIsMountableWithResourceHasMountableObjectType(): void
    {
        $resource = $this->createResource('/event', 'event');
        $this->resourceLoader->method('load')->willReturn($resource);

        $this->assertTrue($this->mountService->isMountable('/path'));
    }

    public function testIsMountableWithLoadException(): void
    {
        $this->resourceLoader->method('load')->willThrowException(new ResourceNotFoundException(ResourceLocation::ofPath('/test')));
        $this->assertFalse($this->mountService->isMountable('/path'));
    }


    public function testIsMountableWithResourceIsInMicrositeNavigation(): void
    {
        $resource = $this->createResource('/event', '', [
            'base' => [
                'trees' => [
                    'navigation' => [
                        'parents' => [
                            ['siteGroup' => ['id' => 123]],
                        ],
                    ],
                ],
            ],
        ]);
        $this->resourceLoader->method('load')->willReturn($resource);

        $this->assertTrue($this->mountService->isMountable('/path'));
    }

    /**
     * @param array<string,mixed> $data
     */
    private function createResource(string $location, string $objectType = '', array $data = []): Resource
    {
        return new Resource(
            location: $location,
            id: '123',
            name: '',
            objectType: $objectType,
            lang: ResourceLanguage::default(),
            data: new DataBag($data),
        );
    }
}
