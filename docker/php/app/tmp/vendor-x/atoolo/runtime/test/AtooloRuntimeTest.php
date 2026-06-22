<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Test;

use Atoolo\Runtime\AtooloRuntime;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AtooloRuntime::class)]
class AtooloRuntimeTest extends TestCase
{
    public function testExecute(): void
    {
        $projectDir = '/path/to/project';
        $options = [
            'package1' => [
                'executor' => [
                    AtooloRuntimeTestExecutor::class,
                ],
            ],
        ];

        $runtime = new AtooloRuntime($projectDir, $options);
        $runtime->run();
        $this->assertTrue(
            AtooloRuntimeTestExecutor::$executed,
            'The executor should have been executed',
        );
    }
}
