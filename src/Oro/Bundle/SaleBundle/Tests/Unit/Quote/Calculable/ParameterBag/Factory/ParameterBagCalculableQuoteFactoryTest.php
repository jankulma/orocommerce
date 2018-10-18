<?php

namespace Oro\Bundle\SaleBundle\Tests\Unit\Quote\Calculable\ParameterBag\Factory;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\SaleBundle\Quote\Calculable\ParameterBag\Factory\ParameterBagCalculableQuoteFactory;
use Oro\Bundle\SaleBundle\Quote\Calculable\ParameterBag\ParameterBagCalculableQuote;
use Oro\Bundle\ShippingBundle\Context\ShippingLineItemInterface;

class ParameterBagCalculableQuoteFactoryTest extends \PHPUnit\Framework\TestCase
{
    public function testCreateCalculableQuote()
    {
        $lineItems = new ArrayCollection(
            [
                $this->getShippingLineItemMock(),
            ]
        );

        $expectedCalculableQuote = new ParameterBagCalculableQuote(
            [
                ParameterBagCalculableQuote::FIELD_LINE_ITEMS => $lineItems,
            ]
        );

        $parameterBagCalculableQuoteFactory = new ParameterBagCalculableQuoteFactory();

        $this->assertEquals(
            $expectedCalculableQuote,
            $parameterBagCalculableQuoteFactory->createCalculableQuote($lineItems)
        );
    }

    /**
     * @return ShippingLineItemInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private function getShippingLineItemMock()
    {
        return $this
            ->getMockBuilder(ShippingLineItemInterface::class)
            ->getMock();
    }
}
