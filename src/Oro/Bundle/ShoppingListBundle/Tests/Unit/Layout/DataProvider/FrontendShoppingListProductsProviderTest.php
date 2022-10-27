<?php

namespace Oro\Bundle\ShoppingListBundle\Tests\Unit\Layout\DataProvider;

use Oro\Bundle\PricingBundle\Formatter\ProductPriceFormatter;
use Oro\Bundle\PricingBundle\Provider\FrontendProductPricesDataProvider;
use Oro\Bundle\ShoppingListBundle\DataProvider\ShoppingListLineItemsDataProvider;
use Oro\Bundle\ShoppingListBundle\Entity\LineItem;
use Oro\Bundle\ShoppingListBundle\Entity\Repository\LineItemRepository;
use Oro\Bundle\ShoppingListBundle\Entity\ShoppingList;
use Oro\Bundle\ShoppingListBundle\Layout\DataProvider\FrontendShoppingListProductsProvider;
use Oro\Component\Testing\Unit\EntityTrait;

class FrontendShoppingListProductsProviderTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /**
     * @var LineItemRepository|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $lineItemRepository;

    /**
     * @var FrontendProductPricesDataProvider|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $frontendProductPricesDataProvider;

    /**
     * @var  FrontendShoppingListProductsProvider
     */
    protected $provider;

    /**
     * @var ShoppingListLineItemsDataProvider|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $shoppingListLineItemsDataProvider;

    /**
     * @var ProductPriceFormatter|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $productPriceFormatter;

    protected function setUp(): void
    {
        $this->lineItemRepository = $this->createMock(LineItemRepository::class);
        $this->frontendProductPricesDataProvider = $this->createMock(FrontendProductPricesDataProvider::class);
        $this->shoppingListLineItemsDataProvider = $this->createMock(ShoppingListLineItemsDataProvider::class);
        $this->productPriceFormatter = $this->createMock(ProductPriceFormatter::class);

        $this->provider = new FrontendShoppingListProductsProvider(
            $this->lineItemRepository,
            $this->frontendProductPricesDataProvider,
            $this->shoppingListLineItemsDataProvider,
            $this->productPriceFormatter
        );
    }

    public function testGetAllPrices()
    {
        /** @var ShoppingList $shoppingList */
        $shoppingList = $this->getEntity('Oro\Bundle\ShoppingListBundle\Entity\ShoppingList', ['id' => 2]);

        /** @var LineItem[] $lineItems */
        $lineItems = [
            $this->getEntity('Oro\Bundle\ShoppingListBundle\Entity\LineItem', ['id' => 1]),
        ];
        $prices = ['price_1', 'price_2'];
        $expected = ['price_1', 'price_2'];

        $this->shoppingListLineItemsDataProvider->expects($this->once())
            ->method('getShoppingListLineItems')
            ->with($shoppingList)
            ->willReturn($lineItems);

        $this->frontendProductPricesDataProvider
            ->expects($this->once())
            ->method('getProductsAllPrices')
            ->with($lineItems)
            ->willReturn($prices);

        $this->productPriceFormatter->expects($this->once())
            ->method('formatProducts')
            ->with($prices)
            ->willReturn($expected);

        $result = $this->provider->getAllPrices($shoppingList);
        $this->assertEquals($expected, $result);
    }

    public function testGetAllPricesWithoutShoppingList()
    {
        $this->shoppingListLineItemsDataProvider->expects($this->never())
            ->method('getShoppingListLineItems');
        $this->frontendProductPricesDataProvider->expects($this->never())
            ->method('getProductsAllPrices');
        $this->productPriceFormatter->expects($this->never())
            ->method('formatProducts');

        $this->provider->getAllPrices();
    }

    /**
     * @dataProvider matchedPriceDataProvider
     * @param ShoppingList|null $shoppingList
     */
    public function testGetMatchedPrice($shoppingList)
    {
        $expected = null;

        if ($shoppingList) {
            $lineItems = [];

            $this->shoppingListLineItemsDataProvider->expects($this->once())
                ->method('getShoppingListLineItems')
                ->willReturn($lineItems);

            $expected = 'expectedData';
            $this->frontendProductPricesDataProvider
                ->expects($this->once())
                ->method('getProductsMatchedPrice')
                ->with($lineItems)
                ->willReturn($expected);
        }

        $result = $this->provider->getMatchedPrice($shoppingList);
        $this->assertEquals($expected, $result);
    }

    public function testGetMatchedPrices()
    {
        $shoppingList = $this->getEntity(ShoppingList::class, ['id' => 42]);
        $lineItems = [];

        $this->shoppingListLineItemsDataProvider->expects($this->once())
            ->method('getShoppingListLineItems')
            ->willReturn($lineItems);

        $expected = ['expectedData'];
        $this->frontendProductPricesDataProvider
            ->expects($this->once())
            ->method('getProductsMatchedPrice')
            ->with($lineItems)
            ->willReturn($expected);

        $result = $this->provider->getMatchedPrices([$shoppingList]);
        $this->assertEquals([42 => $expected], $result);
    }

    /**
     * @return array
     */
    public function matchedPriceDataProvider()
    {
        return [
            'with shoppingList' => [
                'entity' => new ShoppingList(),
            ],
            'without shoppingList' => [
                'entity' => null,
            ],
        ];
    }

    public function testGetLastProductsGroupedByShoppingList()
    {
        $shoppingLists = [$this->getEntity('Oro\Bundle\ShoppingListBundle\Entity\ShoppingList')];
        $productCount = 1;
        $localization = $this->getEntity('Oro\Bundle\LocaleBundle\Entity\Localization');
        $this->lineItemRepository->expects($this->once())
            ->method('getLastProductsGroupedByShoppingList')
            ->with($shoppingLists, $productCount, $localization);

        $this->provider->getLastProductsGroupedByShoppingList($shoppingLists, $productCount, $localization);
    }
}
