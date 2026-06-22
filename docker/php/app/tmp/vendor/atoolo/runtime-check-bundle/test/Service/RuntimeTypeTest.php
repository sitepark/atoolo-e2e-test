<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Check\Test\Service;

use Atoolo\Runtime\Check\Service\RuntimeType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RuntimeType::class)]
class RuntimeTypeTest extends TestCase
{
    public function testOtherCases(): void
    {
        $cases = RuntimeType::otherCases(RuntimeType::CLI);
        $this->assertEquals(
            [RuntimeType::FPM_FCGI, RuntimeType::WORKER],
            $cases,
            'Cases are not as expected',
        );
    }
}
