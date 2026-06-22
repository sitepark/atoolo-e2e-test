<?php

declare(strict_types=1);

namespace Atoolo\Microsite\Test\Factory;

use Atoolo\Microsite\Environment\MicrositeContext;
use Atoolo\Microsite\Factory\MountServiceFactory;
use Atoolo\Microsite\Service\MountService;
use Atoolo\Microsite\Service\Platform;
use Atoolo\Resource\ResourceLoader;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

#[CoversClass(MountServiceFactory::class)]
class MountServiceFactoryTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testCreate(): void
    {
        $micrositeContext = $this->createStub(MicrositeContext::class);
        $resourceLoader = $this->createStub(ResourceLoader::class);
        $platform = $this->createStub(Platform::class);
        $logger = $this->createStub(\Psr\Log\LoggerInterface::class);

        $factory = new MountServiceFactory($micrositeContext, $resourceLoader, $platform, $logger);
        $service = $factory->create();

        $this->assertInstanceOf(MountService::class, $service, 'Service should be an instance of MountService');
    }


    public function testCreateWithoutContext(): void
    {
        $resourceLoader = $this->createStub(ResourceLoader::class);
        $platform = $this->createStub(Platform::class);
        $logger = $this->createStub(\Psr\Log\LoggerInterface::class);

        $factory = new MountServiceFactory(null, $resourceLoader, $platform, $logger);
        $service = $factory->create();

        $this->assertNull($service, 'Service should be null when no context is provided');
    }

}
