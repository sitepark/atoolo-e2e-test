<?php

declare(strict_types=1);

namespace Atoolo\Form\Test\Service\Email;

use Atoolo\Form\Dto\Email\EmailHtmlMessageRendererResult;
use Atoolo\Form\Service\Email\EmailMessageTwigRenderer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Twig\Environment;

#[CoversClass(EmailMessageTwigRenderer::class)]
class EmailMessageTwigRendererTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testRender(): void
    {
        $twig = $this->createStub(Environment::class);
        $twig->method('render')
            ->willReturn('html');

        $renderer = new EmailMessageTwigRenderer($twig);

        $model = [
            'items' => [
                [
                    'type' => 'file',
                    'value' => 'file1',
                ],
            ],
        ];

        $expected = new EmailHtmlMessageRendererResult(
            message: 'html',
            attachments: ['file1'],
        );

        $this->assertEquals($expected, $renderer->render('html', $model), 'unexpected result');
    }
}
