<?php

declare(strict_types=1);

namespace Atoolo\Form\Service\Email;

use Atoolo\Form\Dto\Email\EmailHtmlMessageRendererResult;

abstract class EmailMessageRenderer
{
    /**
     * @param EmailMessageModel $model
     * @return EmailHtmlMessageRendererResult
     */
    abstract public function render(string $format, array $model): EmailHtmlMessageRendererResult;

    /**
     * @param array<EmailMessageModelItem> $model
     * @return array<EmailMessageModelFileUpload>
     */
    protected function findAttachments(array $model): array
    {
        return array_map(static function ($element) {
            return $element['value'];
        }, $this->findByType($model, 'file'));
    }

    protected function findByType(array $model, string $type): array
    {
        $results = [];
        foreach ($model as $item) {
            if (($item['type'] ?? '') === $type) {
                $results[] =  [$item];
            } elseif (isset($item['items']) && is_array($item['items'])) {
                $results[] = $this->findByType($item['items'], $type);
            }
        }

        return array_merge(...$results);
    }
}
