<?php

declare(strict_types=1);

namespace Atoolo\Form\Test\Service\Email;

use Atoolo\Form\Service\Email\CsvGenerator;
use League\Csv\CannotInsertRecord;
use League\Csv\Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CsvGenerator::class)]
class CsvGeneratorTest extends TestCase
{
    /**
     * @throws CannotInsertRecord
     * @throws Exception
     */
    public function testGenerate(): void
    {
        $csvGenerator = new CsvGenerator();
        $model = [
            'items' => [
                [
                    'layout' => true,
                    'items' => [
                        [
                            'label' => 'Name',
                            'value' => 'John Doe',
                        ],
                        [
                            'label' => 'Email',
                            'value' => 'test@example.com',
                        ],
                        [
                            'label' => 'Values',
                            'value' => ['a', 'b'],
                        ],
                    ],
                ],
            ],
        ];

        $csv = $csvGenerator->generate($model);
        $expectedCsv = <<<CSV
Name,Email,Values
"John Doe",test@example.com,"a, b"

CSV;
        $this->assertEquals($expectedCsv, $csv, 'unexpected csv');
    }
}
