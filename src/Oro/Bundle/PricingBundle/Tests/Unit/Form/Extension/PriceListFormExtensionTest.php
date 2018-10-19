<?php

namespace Oro\Bundle\PricingBundle\Tests\Unit\Form\Extension;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FormBundle\Form\Extension\SortableExtension;
use Oro\Bundle\PricingBundle\Form\Extension\PriceListFormExtension;
use Oro\Bundle\PricingBundle\Form\Type\PriceListSelectWithPriorityType;
use Oro\Bundle\PricingBundle\PricingStrategy\MergePricesCombiningStrategy;
use Oro\Bundle\PricingBundle\Tests\Unit\Form\Extension\Stub\PriceListSelectWithPriorityTypeStub;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;

class PriceListFormExtensionTest extends FormIntegrationTestCase
{
    /**
     * @var PriceListFormExtension
     */
    protected $priceListFormExtension;

    /**
     * @var ConfigManager $configManager
     */
    protected $configManager;

    protected function setUp()
    {
        $this->configManager = $this->getMockBuilder(ConfigManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->priceListFormExtension = new PriceListFormExtension($this->configManager);

        parent::setUp();
    }

    public function testBuildForm()
    {
        $this->configManager->expects($this->once())
            ->method('get')
            ->with('oro_pricing.price_strategy')
            ->willReturn(MergePricesCombiningStrategy::NAME);

        $form = $this->factory->create(PriceListSelectWithPriorityType::class, [], []);
        $this->assertTrue($form->has(PriceListFormExtension::MERGE_ALLOWED_FIELD));
    }

    public function testGetExtendedType()
    {
        $this->assertEquals(PriceListSelectWithPriorityType::class, $this->priceListFormExtension->getExtendedType());
    }

    /**
     * @return array
     */
    protected function getExtensions()
    {
        $extensions = [
            new PreloadedExtension(
                [
                    PriceListSelectWithPriorityType::class => new PriceListSelectWithPriorityTypeStub()
                ],
                [
                    PriceListSelectWithPriorityTypeStub::class => [$this->priceListFormExtension],
                    [FormType::class => [new SortableExtension()]]

                ]
            )
        ];

        return $extensions;
    }
}
