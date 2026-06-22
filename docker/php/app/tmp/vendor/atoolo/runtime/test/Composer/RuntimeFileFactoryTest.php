<?php

declare(strict_types=1);

namespace Atoolo\Runtime\Test\Composer;

use Atoolo\Runtime\Composer\RuntimeFile;
use Atoolo\Runtime\Composer\RuntimeFileFactory;
use Composer\Composer;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RuntimeFileFactory::class)]
class RuntimeFileFactoryTest extends TestCase
{
    public function testCreate(): void
    {
        $factory = new RuntimeFileFactory();
        $composer = $this->createStub(Composer::class);
        $runtimeFile = $factory->create(
            $composer,
            '',
        );
        self::assertInstanceOf(RuntimeFile::class, $runtimeFile);
    }
}
