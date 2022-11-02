<?php

namespace Oro\Bundle\WebCatalogBundle\Tests\Unit\DependencyInjection;

use Oro\Bundle\WebCatalogBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\Processor;

class ConfigurationTest extends \PHPUnit\Framework\TestCase
{
    public function testGetConfigTreeBuilder()
    {
        $configuration = new Configuration();

        $treeBuilder = $configuration->getConfigTreeBuilder();
        $this->assertInstanceOf(TreeBuilder::class, $treeBuilder);
    }

    public function testProcessConfiguration()
    {
        $configuration = new Configuration();
        $processor     = new Processor();

        $expected = [
            'settings' => [
                'resolved' => 1,
                'web_catalog' => [
                    'value' => null,
                    'scope' => 'app'
                ],
                'navigation_root' => [
                    'value' => null,
                    'scope' => 'app'
                ],
                'enable_web_catalog_canonical_url' => [
                    'value' => true,
                    'scope' => 'app'
                ]
            ]
        ];

        $this->assertEquals($expected, $processor->processConfiguration($configuration, []));
    }
}
