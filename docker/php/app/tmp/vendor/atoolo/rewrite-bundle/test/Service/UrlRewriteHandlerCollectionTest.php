<?php

declare(strict_types=1);

namespace Atoolo\Rewrite\Test\Service;

use Atoolo\Rewrite\Dto\Url;
use Atoolo\Rewrite\Dto\UrlBuilder;
use Atoolo\Rewrite\Dto\UrlRewriteOptions;
use Atoolo\Rewrite\Dto\UrlRewriteType;
use Atoolo\Rewrite\Service\UrlRewriteHandlerCollection;
use Atoolo\Rewrite\Service\UrlRewriterHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(UrlRewriteHandlerCollection::class)]
class UrlRewriteHandlerCollectionTest extends TestCase
{
    public function testRewrite(): void
    {
        $handler = $this->createMock(UrlRewriterHandler::class);
        $handler->expects(self::once())
            ->method('rewrite')
            ->willReturn(
                Url::builder()
                    ->parse('http://example.com/rewrite-test.php')
                    ->build(),
            );
        $rewriter = new UrlRewriteHandlerCollection([$handler]);

        $url = $rewriter->rewrite(UrlRewriteType::LINK, 'http://example.com/test.php', UrlRewriteOptions::none());

        $this->assertEquals(
            'http://example.com/rewrite-test.php',
            $url,
            'The URL should be rewritten by the handler.',
        );
    }
}
