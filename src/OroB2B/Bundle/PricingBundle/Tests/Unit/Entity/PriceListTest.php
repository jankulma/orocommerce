<?php

namespace OroB2B\Bundle\PricingBundle\Tests\Unit\Entity;

use Oro\Component\Testing\Unit\EntityTestCase;

use OroB2B\Bundle\PricingBundle\Entity\PriceList;
use OroB2B\Bundle\PricingBundle\Entity\PriceListCurrency;

class PriceListTest extends EntityTestCase
{
    public function testAccessors()
    {
        $this->assertPropertyAccessors(
            $this->createPriceList(),
            [
                ['id', 42],
                ['name', 'new price list'],
                ['default', false]
            ]
        );
    }

    public function testCurrenciesCollection()
    {
        $priceList = $this->createPriceList();

        $this->assertInstanceOf(
            'Doctrine\Common\Collections\ArrayCollection',
            $priceList->getCurrencies()
        );
        $this->assertCount(0, $priceList->getCurrencies());

        $currency = $this->createPriceListCurrency();

        $this->assertInstanceOf(
            'OroB2B\Bundle\PricingBundle\Entity\PriceList',
            $priceList->addCurrency($currency)
        );
        $this->assertCount(1, $priceList->getCurrencies());

        $priceList->addCurrency($currency);
        $this->assertCount(1, $priceList->getCurrencies());

        $priceList->removeCurrency($currency);
        $this->assertCount(0, $priceList->getCurrencies());
    }

    /**
     * @return PriceList
     */
    protected function createPriceList()
    {
        return new PriceList();
    }

    /**
     * @return PriceListCurrency
     */
    protected function createPriceListCurrency()
    {
        return new PriceListCurrency();
    }
}
