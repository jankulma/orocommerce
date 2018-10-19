<?php

namespace Oro\Bundle\ProductBundle\Tests\Unit\DependencyInjection\CompilerPass;

use Oro\Bundle\ProductBundle\DependencyInjection\CompilerPass\ComponentProcessorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ComponentProcessorPassTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ComponentProcessorPass
     */
    protected $compilerPass;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ContainerBuilder
     */
    protected $container;

    protected function setUp()
    {
        $this->container = $this
            ->getMockBuilder('Symfony\Component\DependencyInjection\ContainerBuilder')
            ->getMock();

        $this->compilerPass = new ComponentProcessorPass();
    }

    protected function tearDown()
    {
        unset($this->container, $this->compilerPass);
    }

    public function testServiceNotExists()
    {
        $this->container->expects($this->once())
            ->method('hasDefinition')
            ->with($this->equalTo(ComponentProcessorPass::REGISTRY_SERVICE))
            ->will($this->returnValue(false));

        $this->container->expects($this->never())
            ->method('getDefinition');

        $this->container->expects($this->never())
            ->method('findTaggedServiceIds');

        $this->compilerPass->process($this->container);
    }

    public function testServiceExistsNotTaggedServices()
    {
        $this->container->expects($this->once())
            ->method('hasDefinition')
            ->with($this->equalTo(ComponentProcessorPass::REGISTRY_SERVICE))
            ->will($this->returnValue(true));

        $this->container->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with($this->equalTo(ComponentProcessorPass::TAG))
            ->will($this->returnValue([]));

        $this->container->expects($this->never())
            ->method('getDefinition');

        $this->compilerPass->process($this->container);
    }

    public function testServiceExistsWithTaggedServices()
    {
        $this->container->expects($this->once())
            ->method('hasDefinition')
            ->with($this->equalTo(ComponentProcessorPass::REGISTRY_SERVICE))
            ->will($this->returnValue(true));

        $this->container->expects($this->once())
            ->method('findTaggedServiceIds')
            ->with($this->equalTo(ComponentProcessorPass::TAG))
            ->will($this->returnValue(['service' => ['class' => '\stdClass']]));

        $definition = $this->createMock('Symfony\Component\DependencyInjection\Definition');

        $this->container->expects($this->once())
            ->method('getDefinition')
            ->with($this->equalTo(ComponentProcessorPass::REGISTRY_SERVICE))
            ->will($this->returnValue($definition));

        $definition->expects($this->once())
            ->method('addMethodCall')
            ->with('addProcessor', $this->isType('array'));

        $this->compilerPass->process($this->container);
    }
}
