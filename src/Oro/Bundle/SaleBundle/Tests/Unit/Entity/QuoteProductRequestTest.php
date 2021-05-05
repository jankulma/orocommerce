<?php

namespace Oro\Bundle\SaleBundle\Tests\Unit\Entity;

use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\ProductBundle\Entity\ProductUnit;
use Oro\Bundle\RFPBundle\Entity\RequestProductItem;
use Oro\Bundle\SaleBundle\Entity\QuoteProduct;
use Oro\Bundle\SaleBundle\Entity\QuoteProductRequest;
use Oro\Component\Testing\ReflectionUtil;
use Oro\Component\Testing\Unit\EntityTestCaseTrait;

class QuoteProductRequestTest extends \PHPUnit\Framework\TestCase
{
    use EntityTestCaseTrait;

    public function testProperties()
    {
        $properties = [
            ['id', 123],
            ['quoteProduct', new QuoteProduct()],
            ['quantity', 11],
            ['productUnit', new ProductUnit()],
            ['productUnitCode', 'unit-code'],
            ['price', new Price()],
            ['requestProductItem', new RequestProductItem()],
        ];

        static::assertPropertyAccessors(new QuoteProductRequest(), $properties);
    }

    public function testPostLoad()
    {
        $item = new QuoteProductRequest();

        $this->assertNull($item->getPrice());

        ReflectionUtil::setPropertyValue($item, 'value', 10);
        ReflectionUtil::setPropertyValue($item, 'currency', 'USD');

        $item->postLoad();

        $this->assertEquals(Price::create(10, 'USD'), $item->getPrice());
    }

    public function testUpdatePrice()
    {
        $item = new QuoteProductRequest();
        $item->setPrice(Price::create(11, 'EUR'));

        $item->updatePrice();

        $this->assertEquals(11, ReflectionUtil::getPropertyValue($item, 'value'));
        $this->assertEquals('EUR', ReflectionUtil::getPropertyValue($item, 'currency'));
    }

    public function testSetPrice()
    {
        $price = Price::create(22, 'EUR');

        $item = new QuoteProductRequest();
        $item->setPrice($price);

        $this->assertEquals($price, $item->getPrice());

        $this->assertEquals(22, ReflectionUtil::getPropertyValue($item, 'value'));
        $this->assertEquals('EUR', ReflectionUtil::getPropertyValue($item, 'currency'));
    }

    public function testSetProductUnit()
    {
        $item = new QuoteProductRequest();

        $this->assertNull($item->getProductUnitCode());

        $item->setProductUnit((new ProductUnit())->setCode('kg'));

        $this->assertEquals('kg', $item->getProductUnitCode());
    }
}
