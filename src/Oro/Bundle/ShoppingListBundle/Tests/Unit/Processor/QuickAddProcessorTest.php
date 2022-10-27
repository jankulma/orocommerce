<?php

namespace Oro\Bundle\ShoppingListBundle\Tests\Unit\Processor;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ProductBundle\Storage\ProductDataStorage;
use Oro\Bundle\ShoppingListBundle\Entity\ShoppingList;
use Oro\Bundle\ShoppingListBundle\Processor\QuickAddProcessor;
use Oro\Component\Testing\ReflectionUtil;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class QuickAddProcessorTest extends AbstractQuickAddProcessorTest
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->processor = new QuickAddProcessor(
            $this->handler,
            $this->registry,
            $this->messageGenerator,
            $this->aclHelper
        );
        $this->processor->setProductClass(Product::class);
    }

    /**
     * {@inheritdoc}
     */
    public function getProcessorName(): string
    {
        return QuickAddProcessor::NAME;
    }

    /**
     * @dataProvider processDataProvider
     */
    public function testProcess(
        array $data,
        Request $request,
        array $productIds = [],
        array $productQuantities = [],
        bool $failed = false
    ) {
        $entitiesCount = count($data);

        $this->handler->expects($this->any())
            ->method('getShoppingList')
            ->willReturnCallback(function ($shoppingListId) {
                $shoppingList = new ShoppingList();
                if ($shoppingListId) {
                    ReflectionUtil::setId($shoppingList, $shoppingListId);
                }

                return $shoppingList;
            });

        $result = [];
        foreach ($productIds as $sku => $id) {
            $result[] = ['id' => $id, 'sku' => $sku];
        }
        $query = $this->createMock(AbstractQuery::class);
        $query->expects($this->once())
            ->method('getArrayResult')
            ->willReturn($result);
        $queryBuilder = $this->createMock(QueryBuilder::class);

        $this->productRepository->expects($this->once())
            ->method('getProductsIdsBySkuQueryBuilder')
            ->with(array_column($data[ProductDataStorage::ENTITY_ITEMS_DATA_KEY], 'productSku'))
            ->willReturn($queryBuilder);

        $this->aclHelper->expects($this->once())
            ->method('apply')
            ->with($queryBuilder)
            ->willReturn($query);

        if ($failed) {
            $this->handler->expects($this->once())
                ->method('createForShoppingList')
                ->willThrowException(new AccessDeniedException());
        } else {
            $this->handler->expects($data ? $this->once() : $this->never())
                ->method('createForShoppingList')
                ->with(
                    $this->isInstanceOf(ShoppingList::class),
                    array_values($productIds),
                    $productQuantities
                )
                ->willReturn($entitiesCount);
        }

        if ($failed) {
            $this->assertFlashMessage($request, true);
        } elseif ($entitiesCount) {
            $this->assertFlashMessage($request);
        }

        $this->processor->process($data, $request);
    }

    public function testProcessEmpty()
    {
        $data = [];
        $request = new Request();

        $this->handler->expects($this->never())
            ->method('getShoppingList');

        $this->productRepository->expects($this->never())
            ->method('getProductsIdsBySkuQueryBuilder')
            ->with([]);

        $this->aclHelper->expects($this->never())
            ->method('apply');

        $this->handler->expects($this->never())
            ->method('createForShoppingList');

        $this->processor->process($data, $request);
    }

    private function assertFlashMessage(Request $request, bool $isFailedMessage = false): void
    {
        $message = 'test message';

        $this->messageGenerator->expects($this->once())
            ->method($isFailedMessage ? 'getFailedMessage' : 'getSuccessMessage')
            ->willReturn($message);

        $flashBag = $this->createMock(FlashBagInterface::class);
        $flashBag->expects($this->once())
            ->method('add')
            ->with($isFailedMessage ? 'error' : 'success', $message)
            ->willReturn($flashBag);

        $session = $this->createMock(Session::class);
        $session->expects($this->once())
            ->method('getFlashBag')
            ->willReturn($flashBag);

        $request->setSession($session);
    }

    public function processDataProvider(): array
    {
        return [
            'new shopping list' => [
                [
                    ProductDataStorage::ENTITY_ITEMS_DATA_KEY => [
                        ['productSku' => 'sku1', 'productQuantity' => 2, 'productUnit' => 'item'],
                        ['productSku' => 'sku2', 'productQuantity' => 3, 'productUnit' => 'kg'],
                    ]
                ],
                new Request(),
                ['sku1' => 1, 'sku2' => 2],
                ['SKU1' => ['item' => 2], 'SKU2' => ['kg' => 3]]
            ],
            'shopping list with same products couple of times' => [
                [
                    ProductDataStorage::ENTITY_ITEMS_DATA_KEY => [
                        ['productSku' => 'sku1абв', 'productQuantity' => 2, 'productUnit' => 'item'],
                        ['productSku' => 'sku1Абв', 'productQuantity' => 3, 'productUnit' => 'kg'],
                        ['productSku' => 'sku1абВ', 'productQuantity' => 4, 'productUnit' => 'set'],
                        ['productSku' => 'sku2', 'productQuantity' => 5, 'productUnit' => 'item'],
                        ['productSku' => 'sku2', 'productQuantity' => 6, 'productUnit' => 'kg'],
                    ]
                ],
                new Request(),
                ['sku1абв' => 1, 'sku2' => 2],
                ['SKU1АБВ' => ['item' => 2, 'kg' => 3, 'set' => 4], 'SKU2' => ['item' => 5, 'kg' => 6]]
            ],
            'shopping list with products skus in descending order' => [
                [
                    ProductDataStorage::ENTITY_ITEMS_DATA_KEY => [
                        ['productSku' => 'sku1', 'productQuantity' => 11, 'productUnit' => 'item'],
                        ['productSku' => 'sku2', 'productQuantity' => 12, 'productUnit' => 'kg'],
                    ]
                ],
                new Request(),
                [],
                ['SKU2' => ['kg' => 12], 'SKU1' => ['item' => 11]]
            ],
            'existing shopping list' => [
                [
                    ProductDataStorage::ENTITY_ITEMS_DATA_KEY => [
                        ['productSku' => 'sku1', 'productQuantity' => 2, 'productUnit' => 'item'],
                        ['productSku' => 'sku2', 'productQuantity' => 3, 'productUnit' => 'kg'],
                    ],
                ],
                new Request(['oro_product_quick_add' => ['additional' => 1]]),
                ['sku1' => 1, 'sku2' => 2],
                ['SKU1' => ['item' => 2], 'SKU2' => ['kg' => 3]]
            ],
            'ids sorting' => [
                [
                    ProductDataStorage::ENTITY_ITEMS_DATA_KEY => [
                        ['productSku' => 'sku2', 'productQuantity' => 3, 'productUnit' => 'item'],
                        ['productSku' => 'SKU1', 'productQuantity' => 2, 'productUnit' => 'kg'],
                    ],
                ],
                new Request(['oro_product_quick_add' => ['additional' => 1]]),
                ['sku2' => 2, 'sku1' => 1],
                ['SKU1' => ['kg' => 2], 'SKU2' => ['item' => 3]]
            ],
            'process failed' => [
                [
                    ProductDataStorage::ENTITY_ITEMS_DATA_KEY => [
                        ['productSku' => 'sku1', 'productQuantity' => 2, 'productUnit' => 'item'],
                        ['productSku' => 'sku2', 'productQuantity' => 3, 'productUnit' => 'kg'],
                    ],
                ],
                new Request(),
                ['sku1' => 1, 'sku2' => 2],
                ['SKU1' => ['kg' => 2], 'SKU2' => ['item' => 3]],
                true
            ],
        ];
    }
}
