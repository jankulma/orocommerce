<?php

namespace Oro\Bundle\ProductBundle\Tests\Unit\Autocomplete;

use Oro\Bundle\FormBundle\Autocomplete\SearchHandlerInterface;
use Oro\Bundle\ProductBundle\Autocomplete\ProductVisibilityLimitedWithPricesSearchHandler;
use Oro\Bundle\ProductBundle\Entity\Product;
use PHPUnit\Framework\TestCase;

class ProductVisibilityLimitedWithPricesSearchHandlerTest extends TestCase
{
    /**
     * @var SearchHandlerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $productWithPricesSearchHandler;

    /**
     * @var SearchHandlerInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $productVisibilityLimitedSearchHandler;

    /**
     * @var SearchHandlerInterface
     */
    private $searchHandler;

    protected function setUp()
    {
        $this->productWithPricesSearchHandler = $this->createMock(SearchHandlerInterface::class);
        $this->productVisibilityLimitedSearchHandler = $this->createMock(SearchHandlerInterface::class);

        $this->searchHandler = new ProductVisibilityLimitedWithPricesSearchHandler(
            $this->productWithPricesSearchHandler,
            $this->productVisibilityLimitedSearchHandler
        );
    }

    public function testConvertItem()
    {
        $item = [];
        $this->productWithPricesSearchHandler->expects($this->once())
            ->method('convertItem')
            ->willReturn($item);

        $this->assertSame($item, $this->searchHandler->convertItem($item));
    }

    public function testGetProperties()
    {
        $properties = [];
        $this->productWithPricesSearchHandler->expects($this->once())
            ->method('getProperties')
            ->willReturn($properties);

        $this->assertSame($properties, $this->searchHandler->getProperties());
    }

    public function testGetEntityName()
    {
        $this->productWithPricesSearchHandler->expects($this->once())
            ->method('getEntityName')
            ->willReturn(Product::class);

        $this->assertSame(Product::class, $this->searchHandler->getEntityName());
    }

    public function testSearchNoProductsFound()
    {
        $query = 'test';
        $page = 1;
        $perPage = 1;
        $isId = false;
        $result = [
            'more' => false,
            'results' => []
        ];

        $this->productVisibilityLimitedSearchHandler->expects($this->once())
            ->method('search')
            ->with($query, $page, $perPage, $isId)
            ->willReturn($result);

        $this->productWithPricesSearchHandler->expects($this->never())
            ->method('search');

        $this->assertSame($result, $this->searchHandler->search($query, $page, $perPage, $isId));
    }

    /**
     * @dataProvider searchDataProvider
     * @param array $result
     * @param array $pricesResult
     * @param array $expected
     */
    public function testSearch(array $result, array $pricesResult, array $expected)
    {
        $query = 'test';
        $page = 1;
        $perPage = 1;
        $isId = false;

        $this->productVisibilityLimitedSearchHandler->expects($this->once())
            ->method('search')
            ->with($query, $page, $perPage, $isId)
            ->willReturn($result);

        $this->productWithPricesSearchHandler->expects($this->once())
            ->method('search')
            ->with($query, $page, $perPage, $isId)
            ->willReturn($pricesResult);

        $this->assertSame($expected, $this->searchHandler->search($query, $page, $perPage, $isId));
    }

    /**
     * @return array
     */
    public function searchDataProvider()
    {
        return [
            'pricing results does not match' => [
                [
                    'more' => false,
                    'results' => [
                        [
                            'sku' => 'test',
                            'name' => 'test name'
                        ]
                    ]
                ],
                [
                    'more' => false,
                    'results' => [
                        ['sku' => 'test2', 'name' => 'test name', 'prices' => [['value' => 10, 'unit' => 'item']]]
                    ]
                ],
                [
                    'more' => false,
                    'results' => []
                ]
            ],
            'no pricing results' => [
                [
                    'more' => false,
                    'results' => [['sku' => 'test', 'name' => 'test name']]
                ],
                [
                    'more' => false,
                    'results' => []
                ],
                [
                    'more' => false,
                    'results' => []
                ]
            ],
            'pricing results match' => [
                [
                    'more' => false,
                    'results' => [['sku' => 'test', 'name' => 'test name']]
                ],
                [
                    'more' => false,
                    'results' => [
                        ['sku' => 'test', 'name' => 'test name', 'prices' => [['value' => 10, 'unit' => 'item']]]
                    ]
                ],
                [
                    'more' => false,
                    'results' => [
                        ['sku' => 'test', 'name' => 'test name', 'prices' => [['value' => 10, 'unit' => 'item']]]
                    ]
                ]
            ],
        ];
    }
}
