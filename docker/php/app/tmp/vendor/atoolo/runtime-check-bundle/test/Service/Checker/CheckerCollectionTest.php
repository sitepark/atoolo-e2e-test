<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Check\Test\Service\Checker;

use Atoolo\Runtime\Check\Service\Checker\Checker;
use Atoolo\Runtime\Check\Service\Checker\CheckerCollection;
use Atoolo\Runtime\Check\Service\CheckStatus;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(CheckerCollection::class)]
class CheckerCollectionTest extends TestCase
{
    public function testCheck(): void
    {
        $checkerA = $this->createStub(Checker::class);
        $checkerA->method('check')->willReturn(
            CheckStatus::createSuccess()->addReport('a', ['a' => 'b']),
        );
        $checkerA->method('getScope')->willReturn('a');

        $checkerCollection = new CheckerCollection([$checkerA]);
        $status = $checkerCollection->check([]);

        $expected = CheckStatus::createSuccess()
            ->addReport('a', ['a' => 'b']);

        $this->assertEquals(
            $expected,
            $status,
            'Status is not as expected',
        );
    }

    public function testSkipCheck(): void
    {
        $checker = $this->createStub(Checker::class);
        $checker->method('check')->willReturn(
            CheckStatus::createSuccess()->addReport('a', ['a' => 'b']),
        );
        $checker->method('getScope')->willReturn('a');

        $checkerCollection = new CheckerCollection([$checker]);
        $status = $checkerCollection->check(['a']);

        $expected = CheckStatus::createSuccess();

        $this->assertEquals(
            $expected,
            $status,
            'checker should be skipped',
        );
    }
}
