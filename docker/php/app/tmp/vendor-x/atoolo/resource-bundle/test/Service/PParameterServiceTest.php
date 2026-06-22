<?php

declare(strict_types=1);

namespace Atoolo\Resource\Test\Service;

use Atoolo\Resource\DataBag;
use Atoolo\Resource\Resource;
use Atoolo\Resource\ResourceHierarchyLoader;
use Atoolo\Resource\ResourceLanguage;
use Atoolo\Resource\ResourceLoader;
use Atoolo\Resource\ResourceLocation;
use Atoolo\Resource\Service\PParameterService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;

#[CoversClass(PParameterService::class)]
class PParameterServiceTest extends TestCase
{
    private ResourceLoader $resourceLoader;
    private ResourceHierarchyLoader $navigationLoader;
    private string $secret;
    private PParameterService $service;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->resourceLoader = $this->createMock(ResourceLoader::class);
        $this->navigationLoader = $this->createMock(ResourceHierarchyLoader::class);
        $this->secret = 'test_secret';
        $this->service = new PParameterService($this->resourceLoader, $this->navigationLoader, $this->secret);
    }

    /**
     * @throws Exception
     */
    public function testGetPParameterForForeignParent()
    {
        $foreignParent = $this->createMock(ResourceLocation::class);
        $resourceLocation = $this->createMock(ResourceLocation::class);
        $path = [
            $this->createResource('id1', 'location1'),
            $this->createResource('id2', 'location2'),
        ];
        $resource = $this->createResource('id3', 'location3');

        $this->resourceLoader->method('load')->willReturn($resource);
        $this->navigationLoader->method('loadPrimaryPath')->willReturn($path);

        $result = $this->service->getPParameterForForeignParent($foreignParent, $resourceLocation);

        $this->assertEquals('sig:McCLYwo4vQOqNINy,id1,location2,id3', $result);
    }

    public function testSignPathParam()
    {
        $result = $this->service->signPathParam('test_path');
        $this->assertEquals('d4MsWPl4VI1FDEws', $result);
    }

    private function createResource(string $id, string $location): Resource
    {
        return new Resource(
            location: $location,
            id: $id,
            name: '',
            objectType: '',
            lang: ResourceLanguage::default(),
            data: new DataBag([]),
        );
    }
}
