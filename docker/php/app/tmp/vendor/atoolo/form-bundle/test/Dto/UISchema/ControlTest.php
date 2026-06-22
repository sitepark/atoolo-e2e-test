<?php

declare(strict_types=1);

namespace Atoolo\Form\Test\Dto\UISchema;

use Atoolo\Form\Dto\UISchema\Control;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Control::class)]
class ControlTest extends TestCase
{
    public function testValidScope(): void
    {
        $control = new Control('#/properties/field-1');
        $this->assertEquals('#/properties/field-1', $control->scope);
    }

    public function testScopeStartsWithSlash(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Control('/properties/field-1');
    }

    public function testScopeStartsWithLetter(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Control('properties/field-1');
    }

}
