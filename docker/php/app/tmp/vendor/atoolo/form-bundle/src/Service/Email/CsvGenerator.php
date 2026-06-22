<?php

declare(strict_types=1);

namespace Atoolo\Form\Service\Email;

use League\Csv\CannotInsertRecord;
use League\Csv\Exception;
use League\Csv\Writer;

class CsvGenerator
{
    /**
     * @param array{
     *     items: ?array<EmailMessageModelItem>,
     * } $model
     * @throws CannotInsertRecord
     * @throws Exception
     */
    public function generate(array $model): string
    {
        $csv = Writer::createFromString();
        $csv->insertOne($this->collect($model['items'] ?? [], 'label'));
        $csv->insertOne($this->collect($model['items'] ?? [], 'value'));
        return $csv->toString();
    }

    /**
     * @param array<EmailMessageModelItem> $items
     * @return array<string>
     */
    private function collect(array $items, string $field): array
    {
        $data = [];
        foreach ($items as $item) {
            if ($item['layout'] ?? false) {
                /** @var EmailMessageModelLayoutItem $item */
                $data[] = $this->collect($item['items'] ?? [], $field);
                continue;
            }
            /** @var array<string,string|array<string>> $item */
            $value = $item[$field] ?? '';
            if (is_array($value)) {
                $value = implode(', ', $value);
            }
            $data[] = [$value];
        }

        return array_merge([], ...$data);
    }
}
