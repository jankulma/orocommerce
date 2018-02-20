<?php

namespace Oro\Bundle\PricingBundle\Tests\Functional\Model;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\CustomerBundle\Entity\Customer;
use Oro\Bundle\CustomerBundle\Entity\CustomerUser;
use Oro\Bundle\PricingBundle\DependencyInjection\Configuration;
use Oro\Bundle\PricingBundle\DependencyInjection\OroPricingExtension;
use Oro\Bundle\PricingBundle\Model\PriceListTreeHandler;
use Oro\Bundle\PricingBundle\Tests\Functional\DataFixtures\LoadCombinedPriceLists;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use Oro\Bundle\WebsiteBundle\Manager\WebsiteManager;
use Oro\Bundle\WebsiteBundle\Tests\Functional\DataFixtures\LoadWebsiteData;

class PriceListTreeHandlerTest extends WebTestCase
{
    /**
     * @var PriceListTreeHandler
     */
    protected $handler;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject|WebsiteManager
     */
    protected $websiteManager;

    /**
     * @var ConfigManager
     */
    protected $configManager;

    protected function setUp()
    {
        $this->initClient();
        $this->client->useHashNavigation(true);

        $this->loadFixtures([LoadCombinedPriceLists::class]);

        $this->websiteManager = $this->getMockBuilder(WebsiteManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        /** @var $configManager ConfigManager */
        $this->configManager = $this->getContainer()->get('oro_config.global');

        $this->handler = new PriceListTreeHandler(
            $this->getContainer()->get('doctrine'),
            $this->websiteManager,
            $this->configManager
        );
    }

    /**
     * @param string $customerReference
     * @param string $expectedPriceListReference
     *
     * @dataProvider customerUserDataProvider
     */
    public function testGetPriceList($customerReference, $expectedPriceListReference)
    {
        $customerUser = new CustomerUser();
        $customerUser->setCustomer($this->getCustomer($customerReference));

        $this->websiteManager->expects($this->any())->method('getCurrentWebsite')
            ->willReturn($this->getReference(LoadWebsiteData::WEBSITE1));

        $this->assertEquals(
            $this->getReference($expectedPriceListReference)->getName(),
            $this->handler->getPriceList($customerUser->getCustomer())->getName()
        );
    }

    /**
     * @param string $customerReference
     * @param string $configPriceListReference
     * @param string $expectedPriceListReference
     *
     * @dataProvider priceListFromConfigDataProvider
     */
    public function testGetPriceListFromConfig(
        $customerReference,
        $configPriceListReference,
        $expectedPriceListReference
    ) {
        $customerUser = new CustomerUser();
        $customerUser->setCustomer($this->getCustomer($customerReference));
        $key = implode(
            ConfigManager::SECTION_MODEL_SEPARATOR,
            [OroPricingExtension::ALIAS, Configuration::COMBINED_PRICE_LIST]
        );
        $configPriceList = $this->getReference($configPriceListReference);
        $this->configManager->set($key, $configPriceList->getId());
        $this->websiteManager->expects($this->any())->method('getCurrentWebsite')
            ->willReturn($this->getReference(LoadWebsiteData::WEBSITE1));

        $this->assertEquals(
            $this->getReference($expectedPriceListReference)->getName(),
            $this->handler->getPriceList($customerUser->getCustomer())->getName()
        );
    }

    /**
     * @return array
     */
    public function customerUserDataProvider()
    {
        return [
            'get PriceList from customer' => ['customer.level_1.2', '2t_3f_1t'],
            'get PriceList from group' => ['customer.level_1.3', '1t_2t_3t'],
            'get PriceList from website' => ['customer.level_1.2.1', '1t_2t_3t'],
            'get PriceList from config' => ['customer.level_1.2.1', '1t_2t_3t'],
        ];
    }

    /**
     * @return array
     */
    public function priceListFromConfigDataProvider()
    {
        return [
            'get PriceList from customer' => ['customer.level_1.2', '1t_2t_3t', '2t_3f_1t'],
            'get PriceList from config' => ['customer.level_1.2.1', '1t_2t_3t', '1t_2t_3t'],
        ];
    }

    /**
     * @param string $reference
     * @return Customer
     */
    protected function getCustomer($reference)
    {
        return $this->getReference($reference);
    }
}
