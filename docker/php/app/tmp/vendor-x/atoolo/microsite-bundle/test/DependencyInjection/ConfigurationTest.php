<?php

declare(strict_types=1);

namespace Atoolo\Microsite\Test\DependencyInjection;

use Atoolo\Microsite\DependencyInjection\Configuration;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

#[CoversClass(Configuration::class)]
class ConfigurationTest extends TestCase
{
    public function testGetConfigTreeBuilder(): void
    {
        $configuration = new Configuration();
        $treeBuilder = $configuration->getConfigTreeBuilder();

        $rootNode = $treeBuilder->buildTree();
        $children = $rootNode->getChildren();

        $this->assertArrayHasKey('mountable_object_types', $children);
        $this->assertEquals([], $children['mountable_object_types']->getDefaultValue());
    }
}
