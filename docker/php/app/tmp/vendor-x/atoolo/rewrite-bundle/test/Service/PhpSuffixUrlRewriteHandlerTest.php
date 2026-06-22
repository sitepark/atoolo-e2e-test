<?php

declare(strict_types=1);

namespace Atoolo\Rewrite\Test\Service;

use Atoolo\Rewrite\Dto\Url;
use Atoolo\Rewrite\Dto\UrlBuilder;
use Atoolo\Rewrite\Dto\UrlRewriteOptions;
use Atoolo\Rewrite\Dto\UrlRewriterHandlerContext;
use Atoolo\Rewrite\Dto\UrlRewriteType;
use Atoolo\Rewrite\Service\PhpSuffixUrlRewriteHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(PhpSuffixUrlRewriteHandler::class)]
class PhpSuffixUrlRewriteHandlerTest extends TestCase
{
    public function testRewritePhpFile(): void
    {
        $handler = new PhpSuffixUrlRewriteHandler();

        $origin = Url::builder()->path('/foo/bar.php')->build();
        $url = $handler->rewrite(
            $origin,
            $this->createContext($origin, UrlRewriteType::LINK),
        );

        $expected = Url::builder()->path('/foo/bar')->build();

        $this->assertEquals(
            $expected,
            $url,
            'The URL should be rewritten by the handler.',
        );
    }

    public function testRewritePhpIndexFile(): void
    {
        $handler = new PhpSuffixUrlRewriteHandler();

        $origin = Url::builder()->path('/foo/bar/index.php')->build();
        $url = $handler->rewrite(
            $origin,
            $this->createContext($origin, UrlRewriteType::LINK),
        );

        $expected = Url::builder()->path('/foo/bar/')->build();

        $this->assertEquals(
            $expected,
            $url,
            'The URL should be rewritten by the handler.',
        );
    }

    public function testRewriteFullQualifiedUrl(): void
    {
        $handler = new PhpSuffixUrlRewriteHandler();

        $origin = Url::builder()->parse('https://www.example.com/foo/bar.php')->build();
        $url = $handler->rewrite(
            $origin,
            $this->createContext($origin, UrlRewriteType::LINK),
        );

        $expected = Url::builder()->parse('https://www.example.com/foo/bar.php')->build();

        $this->assertEquals(
            $expected,
            $url,
            'The URL should not be rewritten by the handler.',
        );
    }

    public function testRewriteNonLink(): void
    {
        $handler = new PhpSuffixUrlRewriteHandler();

        $origin = Url::builder()->parse('/foo/bar.pdf')->build();
        $url = $handler->rewrite(
            $origin,
            $this->createContext($origin, UrlRewriteType::MEDIA),
        );

        $expected = Url::builder()->parse('/foo/bar.pdf')->build();

        $this->assertEquals(
            $expected,
            $url,
            'The URL should not be rewritten by the handler.',
        );
    }

    public function testRewriteWithNullPath(): void
    {
        $handler = new PhpSuffixUrlRewriteHandler();

        $origin = Url::builder()->build();
        $url = $handler->rewrite(
            $origin,
            $this->createContext($origin, UrlRewriteType::LINK),
        );

        $expected = Url::builder()->build();

        $this->assertEquals(
            $expected,
            $url,
            'The URL should not be rewritten by the handler.',
        );
    }

    public function testRewriteLegacyWebIesPath(): void
    {
        $handler = new PhpSuffixUrlRewriteHandler();

        $origin = Url::builder()->path('/WEB-IES/test.php')->build();
        $url = $handler->rewrite(
            $origin,
            $this->createContext($origin, UrlRewriteType::LINK),
        );

        $expected = Url::builder()->path('/WEB-IES/test.php')->build();

        $this->assertEquals(
            $expected,
            $url,
            'The URL should not be rewritten by the handler.',
        );
    }

    public function testRewriteWithoutPhpSuffix(): void
    {
        $handler = new PhpSuffixUrlRewriteHandler();

        $origin = Url::builder()->path('/foo/bar')->build();
        $url = $handler->rewrite(
            $origin,
            $this->createContext($origin, UrlRewriteType::LINK),
        );

        $expected = Url::builder()->path('/foo/bar')->build();

        $this->assertEquals(
            $expected,
            $url,
            'The URL should not be rewritten by the handler.',
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
