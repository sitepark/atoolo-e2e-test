<?php

declare(strict_types=1);

namespace Atoolo\Form\Test\Service\Email;

use Atoolo\Form\Dto\Email\EmailHtmlMessageRendererResult;
use Atoolo\Form\Service\Email\EmailMessageTwigMjmlRenderer;
use Atoolo\Form\Service\Email\MjmlRenderer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

#[CoversClass(EmailMessageTwigMjmlRenderer::class)]
class EmailMessageTwigMjmlRendererTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testRender(): void
    {
        $twig = $this->createStub(Environment::class);
        $twig->method('render')
            ->willReturn('mjml');
        $mjml = $this->createStub(MjmlRenderer::class);
        $mjml->method('render')
            ->willReturn('html');

        $renderer = new EmailMessageTwigMjmlRenderer($twig, $mjml);
        $expected = new EmailHtmlMessageRendererResult(
            message: 'html',
            attachments: [],
        );
        $this->assertEquals($expected, $renderer->render('html', ['items' => []]), 'unexpected result');
    }
}
