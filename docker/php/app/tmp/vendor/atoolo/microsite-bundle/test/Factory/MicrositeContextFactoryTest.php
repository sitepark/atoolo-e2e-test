<?php

declare(strict_types=1);

namespace Atoolo\Microsite\Test\Factory;

use Atoolo\Microsite\Environment\MicrositeContext;
use Atoolo\Microsite\Factory\MicrositeContextFactory;
use Atoolo\Resource\DataBag;
use Atoolo\Resource\Resource;
use Atoolo\Resource\ResourceChannel;
use Atoolo\Resource\ResourceHierarchyLoader;
use Atoolo\Resource\ResourceLanguage;
use Atoolo\Resource\ResourceTenant;
use Atoolo\Rewrite\Service\UrlRewriteContext;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\ServerBag;

#[CoversClass(MicrositeContextFactory::class)]
class MicrositeContextFactoryTest extends TestCase
{
    private RequestStack $requestStack;
    private ResourceChannel $resourceChannel;
    private UrlRewriteContext $rewriteContext;
    private ResourceHierarchyLoader $navigationHierarchyLoader;
    private array $mountableObjectTypes;
    private MicrositeContextFactory $factory;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->requestStack = $this->createMock(RequestStack::class);
        $this->resourceChannel = new ResourceChannel(
            id: '',
            name: '',
            anchor: '',
            serverName: '',
            isPreview: false,
            nature: '',
            locale: '',
            baseDir: '',
            resourceDir: '/resource/dir',
            configDir: '',
            searchIndex: '',
            translationLocales: [],
            attributes: new DataBag([]),
            tenant: $this->createStub(ResourceTenant::class),
        );
        $this->rewriteContext = $this->createMock(UrlRewriteContext::class);
        $this->navigationHierarchyLoader = $this->createMock(ResourceHierarchyLoader::class);
        $this->mountableObjectTypes = ['type1', 'type2'];

        $this->factory = new MicrositeContextFactory(
            $this->requestStack,
            $this->resourceChannel,
            $this->rewriteContext,
            $this->navigationHierarchyLoader,
            $this->mountableObjectTypes,
        );
    }

    public function testCreateReturnsNullWhenNoRequest(): void
    {
        $this->requestStack->method('getCurrentRequest')->willReturn(null);

        $this->assertNull($this->factory->create());
    }

    /**
     * @throws Exception
     */
    public function testCreateReturnsNullWhenMicrositeHostIsEmpty(): void
    {
        $request = $this->createMock(Request::class);
        $request->server = $this->createMock(ServerBag::class);
        $request->server->method('getString')->willReturnMap([
            ['ATOOLO_MICROSITE_HOST', 'test.example.com'],
            ['ATOOLO_MICROSITE_PATH', '/microsite/test'],
            ['ATOOLO_MAIN_HOST', ''],
        ]);

        $this->requestStack->method('getCurrentRequest')->willReturn($request);

        $this->assertNull($this->factory->create());
    }

    /**
     * @throws Exception
     */
    public function testCreateReturnsNullWhenMicrositePathIsEmpty(): void
    {
        $request = $this->createMock(Request::class);
        $request->server = $this->createMock(ServerBag::class);
        $request->server->method('getString')->willReturnMap([
            ['ATOOLO_MICROSITE_HOST', 'test.example.com'],
            ['ATOOLO_MICROSITE_PATH', ''],
            ['ATOOLO_MAIN_HOST', 'example.com'],
        ]);

        $this->requestStack->method('getCurrentRequest')->willReturn($request);

        $this->assertNull($this->factory->create());
    }

    /**
     * @throws Exception
     */
    public function testCreateReturnsMicrositeContext(): void
    {
        $request = $this->createMock(Request::class);
        $request->server = $this->createMock(ServerBag::class);
        $request->server->method('getString')->willReturnMap([
            ['ATOOLO_MICROSITE_HOST', 'test.example.com'],
            ['ATOOLO_MICROSITE_PATH', '/microsite/test'],
            ['ATOOLO_MAIN_HOST', 'example.com'],
        ]);

        $this->requestStack->method('getCurrentRequest')->willReturn($request);

        $this->rewriteContext->method('getBasePath')->willReturn('/base/path');

        $root = new Resource(
            location: '',
            id: '123',
            name: '',
            objectType: '',
            lang: $this->createStub(ResourceLanguage::class),
            data: new DataBag([]),
        );

        $this->navigationHierarchyLoader->method('loadRoot')->willReturn($root);

        $context = $this->factory->create();

        $expected = new MicrositeContext(
            resourceDir: '/resource/dir',
            currentPath: '/base/path',
            micrositeHost: 'test.example.com',
            micrositePath: '/microsite/test',
            mainHost: 'example.com',
            siteId: 0,
            mountableObjectTypes: $this->mountableObjectTypes,
        );
        $this->assertEquals($expected, $context, 'The created context should match the expected one.');
    }
}
