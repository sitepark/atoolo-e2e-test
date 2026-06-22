<?php

declare(strict_types=1);

namespace Atoolo\Form\Test\Service\Email;

use Atoolo\Form\Dto\Email\EmailHtmlMessageRendererResult;
use Atoolo\Form\Service\Email\EmailMessageRenderer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EmailMessageRenderer::class)]
class EmailMessageRendererTest extends TestCase
{
    public function testFindAttachments(): void
    {
        $model = [
            [
                'type' => 'file',
                'value' => [
                    'filename' => 'file1',
                    'contentType' => 'application/pdf',
                    'data' => 'date',
                    'size' => 4,
                ],
            ],
            [
                'type' => 'text',
                'value' => 'text1',
            ],
        ];

        $renderer = new class extends EmailMessageRenderer {
            /**
             * @param array $model
             * @return array<EmailMessageModelFileUpload>
             */
            public function render(string $format, array $model): EmailHtmlMessageRendererResult
            {
                return new EmailHtmlMessageRendererResult(
                    message: 'html',
                    attachments: $this->findAttachments($model),
                );
            }
        };

        $expected = new EmailHtmlMessageRendererResult(
            message: 'html',
            attachments: [
                [
                    'filename' => 'file1',
                    'contentType' => 'application/pdf',
                    'data' => 'date',
                    'size' => 4,
                ],
            ],
        );

        $this->assertEquals($expected, $renderer->render('html', $model), 'unexpected result');
    }
}
