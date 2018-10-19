<?php

namespace Oro\Bundle\InventoryBundle\Tests\Unit\Form\Extension;

use Oro\Bundle\InventoryBundle\Entity\InventoryLevel;
use Oro\Bundle\InventoryBundle\Form\Extension\InventoryLevelExportTemplateTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;

class InventoryLevelExportTemplateTypeExtensionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var InventoryLevelExportTemplateTypeExtension
     */
    protected $inventoryLevelExportTemplateTypeExtension;

    protected function setUp()
    {
        $this->inventoryLevelExportTemplateTypeExtension = new InventoryLevelExportTemplateTypeExtension();
    }

    public function testBuildFormShouldRemoveDefaultChild()
    {
        $builder = $this->getBuilderMock();

        $builder->expects($this->once())
            ->method('remove')
            ->with('processorAlias');

        $this->inventoryLevelExportTemplateTypeExtension->buildForm(
            $builder,
            ['entityName' => InventoryLevel::class]
        );
    }

    public function testBuildFormShouldCreateCorrectChoices()
    {
        $processorAliases = [
            'oro_product.inventory_status_only_template',
            'oro_inventory.detailed_inventory_levels_template'
        ];

        $builder = $this->getBuilderMock();
        $phpunitTestCase = $this;

        $builder->expects($this->once())
            ->method('add')
            ->will($this->returnCallback(function ($name, $type, $options) use ($phpunitTestCase, $processorAliases) {
                $choices = $options['choices'];
                $phpunitTestCase->assertContains(
                    $processorAliases[0],
                    $choices
                );
                $phpunitTestCase->assertContains(
                    $processorAliases[1],
                    $choices
                );
            }));

        $this->inventoryLevelExportTemplateTypeExtension->buildForm(
            $builder,
            ['entityName' => InventoryLevel::class]
        );
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|FormBuilderInterface
     */
    protected function getBuilderMock()
    {
        return $this->getMockBuilder(FormBuilderInterface::class)->getMock();
    }
}
