<?php

declare(strict_types=1);

namespace Atoolo\Rewrite\Test\Service;

use Atoolo\Resource\DataBag;
use Atoolo\Resource\ResourceChannel;
use Atoolo\Resource\ResourceTenant;
use Atoolo\Rewrite\Dto\Url;
use Atoolo\Rewrite\Dto\UrlRewriteOptions;
use Atoolo\Rewrite\Dto\UrlRewriterHandlerContext;
use Atoolo\Rewrite\Dto\UrlRewriteType;
use Atoolo\Rewrite\Service\LangPrefixUrlRewriteHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

#[CoversClass(LangPrefixUrlRewriteHandler::class)]
class LangPrefixUrlRewriteHandlerTest extends TestCase
{
    private RequestStack $requestStack;
    private Request $request;
    private ResourceChannel $resourceChannel;

    /**
     * @throws Exception
     */
    public function setUp(): void
    {
        $this->request = $this->createStub(Request::class);
        $this->requestStack = $this->createStub(RequestStack::class);
        $this->requestStack->method('getCurrentRequest')->willReturn($this->request);
        $this->resourceChannel = new ResourceChannel(
            id: '',
            name: '',
            anchor: '',
            serverName: '',
            isPreview: false,
            nature: '',
            locale: '',
            baseDir: '',
            resourceDir: '',
            configDir: '',
            searchIndex: '',
            translationLocales: ['en_US', 'it_IT'],
            attributes: new DataBag([]),
            tenant: $this->createMock(ResourceTenant::class),
        );
    }

    public function testRewriteLangPrefix(): void
    {

        $this->request->method('getPathInfo')->willReturn('/en/foo/bar.php');

        $handler = new LangPrefixUrlRewriteHandler(
            $this->requestStack,
            'de:true',
            $this->resourceChannel,
        );

        $origin = Url::builder()->path('/foo/bar.php')->build();
        $url = $handler->rewrite(
            $origin,
            $this->createContext($origin, UrlRewriteType::LINK),
        );

        $expected = Url::builder()->path('/en/foo/bar.php')->build();

        $this->assertEquals(
            $expected,
            $url,
            'The URL should be rewritten by the handler.',
        );
    }

    public function testRewriteLangPrefixWithoutTranslationLocales(): void
    {

        $resourceChannel = new ResourceChannel(
            id: '',
            name: '',
            anchor: '',
            serverName: '',
            isPreview: false,
            nature: '',
            locale: '',
            baseDir: '',
            resourceDir: '',
            configDir: '',
            searchIndex: '',
            translationLocales: [],
            attributes: new DataBag([]),
            tenant: $this->createMock(ResourceTenant::class),
        );

        $this->request->method('getPathInfo')->willReturn('/en/foo/bar.php');

        $handler = new LangPrefixUrlRewriteHandler(
            $this->requestStack,
            'de:true',
            $resourceChannel,
        );

        $origin = Url::builder()->path('/foo/bar.php')->build();
        $url = $handler->rewrite(
            $origin,
            $this->createContext($origin, UrlRewriteType::LINK),
        );

        $expected = Url::builder()->path('/foo/bar.php')->build();

        $this->assertEquals(
            $expected,
            $url,
            'The URL should be rewritten by the handler.',
        );
    }

    public function testRewriteWithPrefixForDefaultLang(): void
    {

        $this->request->method('getPathInfo')->willReturn('/foo/bar.php');

        $handler = new LangPrefixUrlRewriteHandler(
            $this->requestStack,
            'de:true',
            $this->resourceChannel,
        );

        $origin = Url::builder()->path('/foo/bar.php')->build();
        $url = $handler->rewrite(
            $origin,
            $this->createContext($origin, UrlRewriteType::LINK),
        );

        $expected = Url::builder()->path('/de/foo/bar.php')->build();

        $this->assertEquals(
            $expected,
            $url,
            'The URL should be rewritten by the handler.',
        );
    }

    public function testRewriteWithoutPrefixForDefaultLang(): void
    {

        $this->request->method('getPathInfo')->willReturn('/de/foo/bar.php');

        $handler = new LangPrefixUrlRewriteHandler(
            $this->requestStack,
            'de:false',
            $this->resourceChannel,
        );

        $origin = Url::builder()->path('/foo/bar.php')->build();
        $url = $handler->rewrite(
            $origin,
            $this->createContext($origin, UrlRewriteType::LINK),
        );

        $expected = Url::builder()->path('/foo/bar.php')->build();

        $this->assertEquals(
            $expected,
            $url,
            'The URL should be rewritten by the handler.',
        );
    }

    public function testRewriteWithFullQualifiedUrl(): void
    {

        $handler = new LangPrefixUrlRewriteHandler(
            $this->requestStack,
            'de:true',
            $this->resourceChannel,
        );

        $origin = Url::builder()->parse('https://www.example.com/foo/bar.php')->build();
        $url = $handler->rewrite(
            $origin,
            $this->createContext($origin, UrlRewriteType::LINK),
        );

        $expected = Url::builder()->parse('https://www.example.com/foo/bar.php')->build();

        $this->assertEquals(
            $expected,
            $url,
            'The URL should be rewritten by the handler.',
        );
    }

    public function testRewriteWithNonLink(): void
    {

        $handler = new LangPrefixUrlRewriteHandler(
            $this->requestStack,
            'de:true',
            $this->resourceChannel,
        );

        $origin = Url::builder()->path('/foo/bar.pdf')->build();
        $url = $handler->rewrite(
            $origin,
            $this->createContext($origin, UrlRewriteType::MEDIA),
        );

        $expected = Url::builder()->path('/foo/bar.pdf')->build();

        $this->assertEquals(
            $expected,
            $url,
            'The URL should be rewritten by the handler.',
        );
    }

    /**
     * @throws Exception
     */
    public function testRewriteWithNullRequest(): void
    {

        $handler = new LangPrefixUrlRewriteHandler(
            $this->createStub(RequestStack::class),
            'de:false',
            $this->resourceChannel,
        );

        $origin = Url::builder()->path('/foo/bar.php')->build();
        $url = $handler->rewrite(
            $origin,
            $this->createContext($origin, UrlRewriteType::LINK),
        );

        $expected = Url::builder()->path('/foo/bar.php')->build();

        $this->assertEquals(
            $expected,
            $url,
            'The URL should be rewritten by the handler.',
        );
    }

    /**
     * @throws Exception
     */
    public function testRewriteWithEmptyPrefixForDefaultLang(): void
    {

        $handler = new LangPrefixUrlRewriteHandler(
            $this->createStub(RequestStack::class),
            '',
            $this->resourceChannel,
        );

        $origin = Url::builder()->path('/foo/bar.php')->build();
        $url = $handler->rewrite(
            $origin,
            $this->createContext($origin, UrlRewriteType::LINK),
        );

        $expected = Url::builder()->path('/foo/bar.php')->build();

        $this->assertEquals(
            $expected,
            $url,
            'The URL should be rewritten by the handler.',
        );
    }

    /**
     * @throws Exception
     */
    public function testRewriteWithInvalidPrefixForDefaultLang(): void
    {

        $handler = new LangPrefixUrlRewriteHandler(
            $this->createStub(RequestStack::class),
            'invalid-format',
            $this->resourceChannel,
        );

        $origin = Url::builder()->path('/foo/bar.php')->build();
        $url = $handler->rewrite(
            $origin,
            $this->createContext($origin, UrlRewriteType::LINK),
        );

        $expected = Url::builder()->path('/foo/bar.php')->build();

        $this->assertEquals(
            $expected,
            $url,
            'The URL should be rewritten by the handler.',
        );
    }


    private function createContext(Url $origin, UrlRewriteType $type): UrlRewriterHandlerContext
    {
        return new UrlRewriterHandlerContext(
            origin: $origin,
            type: $type,
            options: UrlRewriteOptions::none(),
        );
    }

}
