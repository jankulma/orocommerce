<?php

namespace Oro\Bundle\ShoppingListBundle\Tests\Unit\Processor;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Oro\Bundle\ActionBundle\Model\ActionData;
use Oro\Bundle\ActionBundle\Model\ActionGroup;
use Oro\Bundle\ActionBundle\Model\ActionGroupRegistry;
use Oro\Bundle\LocaleBundle\Formatter\DateTimeFormatterInterface;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ProductBundle\Storage\ProductDataStorage;
use Oro\Bundle\ShoppingListBundle\Entity\ShoppingList;
use Oro\Bundle\ShoppingListBundle\Manager\CurrentShoppingListManager;
use Oro\Bundle\ShoppingListBundle\Manager\ShoppingListLimitManager;
use Oro\Bundle\ShoppingListBundle\Manager\ShoppingListManager;
use Oro\Bundle\ShoppingListBundle\Processor\QuickAddCheckoutProcessor;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

class QuickAddCheckoutProcessorTest extends AbstractQuickAddProcessorTest
{
    /** @var \PHPUnit\Framework\MockObject\MockObject|ShoppingListManager */
    private $shoppingListManager;

    /** @var \PHPUnit\Framework\MockObject\MockObject|ShoppingListLimitManager */
    private $shoppingListLimitManager;

    /** @var \PHPUnit\Framework\MockObject\MockObject|CurrentShoppingListManager */
    private $currentShoppingListManager;

    /** @var \PHPUnit\Framework\MockObject\MockObject|ActionGroupRegistry */
    private $actionGroupRegistry;

    /** @var \PHPUnit\Framework\MockObject\MockObject|ActionGroup */
    private $actionGroup;

    /** @var \PHPUnit\Framework\MockObject\MockObject|TranslatorInterface */
    private $translator;

    /** @var \PHPUnit\Framework\MockObject\MockObject|DateTimeFormatterInterface */
    private $dateFormatter;

    /** @var QuickAddCheckoutProcessor */
    protected $processor;

    protected function setUp(): void
    {
        parent::setUp();

        $this->shoppingListManager = $this->createMock(ShoppingListManager::class);
        $this->shoppingListLimitManager = $this->createMock(ShoppingListLimitManager::class);
        $this->currentShoppingListManager = $this->createMock(CurrentShoppingListManager::class);
        $this->actionGroupRegistry = $this->createMock(ActionGroupRegistry::class);
        $this->actionGroup = $this->createMock(ActionGroup::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->dateFormatter = $this->createMock(DateTimeFormatterInterface::class);

        $this->processor = new QuickAddCheckoutProcessor(
            $this->handler,
            $this->registry,
            $this->messageGenerator,
            $this->aclHelper
        );

        $this->processor->setProductClass(Product::class);
        $this->processor->setShoppingListManager($this->shoppingListManager);
        $this->processor->setShoppingListLimitManager($this->shoppingListLimitManager);
        $this->processor->setCurrentShoppingListManager($this->currentShoppingListManager);
        $this->processor->setActionGroupRegistry($this->actionGroupRegistry);
        $this->processor->setTranslator($this->translator);
        $this->processor->setDateFormatter($this->dateFormatter);
        $this->processor->setActionGroupName('start_shoppinglist_checkout');
    }

    /**
     * {@inheritDoc}
     */
    public function getProcessorName(): string
    {
        return QuickAddCheckoutProcessor::NAME;
    }

    public function testIsAllowed()
    {
        $this->handler->expects($this->once())
            ->method('isAllowed')
            ->willReturn(true);
        $this->actionGroupRegistry->expects($this->once())
            ->method('findByName')
            ->with('start_shoppinglist_checkout')
            ->willReturn($this->actionGroup);

        $this->assertTrue($this->processor->isAllowed());
    }

    public function testIsAllowedAndNoActionGroup()
    {
        $this->handler->expects($this->once())
            ->method('isAllowed')
            ->willReturn(true);
        $this->actionGroupRegistry->expects($this->once())
            ->method('findByName')
            ->with('start_shoppinglist_checkout')
            ->willReturn(null);

        $this->assertFalse($this->processor->isAllowed());
    }

    /**
     * @dataProvider wrongDataDataProvider
     */
    public function testProcessWithNotValidData(array $data)
    {
        $request = $this->createMock(Request::class);

        $this->assertEquals(null, $this->processor->process($data, $request));
    }

    public function wrongDataDataProvider(): array
    {
        return [
            'entity items are not array' => [
                'data' => [
                    ProductDataStorage::ENTITY_ITEMS_DATA_KEY => 'something'
                ]
            ],
            'entity items are not array and empty' => [
                'data' => [
                    ProductDataStorage::ENTITY_ITEMS_DATA_KEY => ''
                ]
            ],
            'entity items are empty' => [
                'data' => [
                    ProductDataStorage::ENTITY_ITEMS_DATA_KEY => []
                ]
            ],
        ];
    }

    public function testProcessWhenCommitted()
    {
        $data = $this->getProductData();

        $productIds = ['sku1абв' => 1, 'sku2' => 2];
        $productUnitsQuantities = ['SKU1АБВ' => ['kg' => 2], 'SKU2' => ['liter' => 3]];

        $this->shoppingListLimitManager->expects($this->once())
            ->method('isReachedLimit')
            ->willReturn(false);

        $shoppingList = new ShoppingList();

        $this->shoppingListManager->expects($this->once())
            ->method('create')
            ->willReturn($shoppingList);

        $this->em->expects($this->once())
            ->method('persist');
        $this->em->expects($this->once())
            ->method('flush');

        $this->dateFormatter->expects($this->once())
            ->method('format')
            ->willReturn('Mar 28, 2016, 2:50 PM');

        $this->translator->expects($this->once())
            ->method('trans')
            ->willReturn('Quick Order (Mar 28, 2016, 2:50 PM)');

        $redirectUrl = '/customer/shoppingList/123';
        $actionData = new ActionData([
            'shoppingList' => $shoppingList,
            'redirectUrl' => $redirectUrl
        ]);

        $query = $this->createMock(AbstractQuery::class);
        $query->expects($this->once())
            ->method('getArrayResult')
            ->willReturn([
                ['id' => 1, 'sku' => 'SKU1АБВ'],
                ['id' => 2, 'sku' => 'SKU2'],
            ]);
        $queryBuilder = $this->createMock(QueryBuilder::class);

        $this->productRepository->expects($this->once())
            ->method('getProductsIdsBySkuQueryBuilder')
            ->with(['sku1абв', 'sku2'])
            ->willReturn($queryBuilder);

        $this->aclHelper->expects($this->once())
            ->method('apply')
            ->with($queryBuilder)
            ->willReturn($query);

        $this->actionGroupRegistry->expects($this->once())
            ->method('findByName')
            ->with('start_shoppinglist_checkout')
            ->willReturn($this->actionGroup);

        $this->actionGroup->expects($this->once())
            ->method('execute')
            ->willReturn($actionData);

        $this->handler->expects($this->once())
            ->method('createForShoppingList')
            ->with(
                $this->isInstanceOf(ShoppingList::class),
                array_values($productIds),
                $productUnitsQuantities
            )
            ->willReturn(count($data));

        $this->em->expects($this->once())
            ->method('commit');

        $request = new Request();
        $request->setSession($this->createMock(Session::class));

        /** @var RedirectResponse $result */
        $result = $this->processor->process($data, $request);
        $this->assertInstanceOf(RedirectResponse::class, $result);
        $this->assertEquals($redirectUrl, $result->getTargetUrl());
    }

    public function testProcessWhenCommittedWithLimit()
    {
        $data = $this->getProductData();

        $productIds = ['sku1абв' => 1, 'sku2' => 2];
        $productUnitsQuantities = ['SKU1АБВ' => ['kg' => 2], 'SKU2' => ['liter' => 3]];

        $this->shoppingListLimitManager->expects($this->once())
            ->method('isReachedLimit')
            ->willReturn(true);

        $shoppingList = new ShoppingList();

        $this->currentShoppingListManager->expects($this->once())
            ->method('getCurrent')
            ->willReturn($shoppingList);

        $this->shoppingListManager->expects($this->once())
            ->method('edit')
            ->willReturn($shoppingList);

        $this->shoppingListManager->expects($this->once())
            ->method('removeLineItems');

        $this->em->expects($this->never())
            ->method('persist');
        $this->em->expects($this->never())
            ->method('flush');

        $query = $this->createMock(AbstractQuery::class);
        $query->expects($this->once())
            ->method('getArrayResult')
            ->willReturn([
                ['id' => 1, 'sku' => 'SKU1АБВ'],
                ['id' => 2, 'sku' => 'SKU2'],
            ]);
        $queryBuilder = $this->createMock(QueryBuilder::class);

        $this->productRepository->expects($this->once())
            ->method('getProductsIdsBySkuQueryBuilder')
            ->with(['sku1абв', 'sku2'])
            ->willReturn($queryBuilder);

        $this->aclHelper->expects($this->once())
            ->method('apply')
            ->with($queryBuilder)
            ->willReturn($query);

        $actionData = new ActionData([
            'shoppingList' => $shoppingList,
            'redirectUrl' => 'some/url'
        ]);

        $this->actionGroupRegistry->expects($this->once())
            ->method('findByName')
            ->with('start_shoppinglist_checkout')
            ->willReturn($this->actionGroup);

        $this->actionGroup->expects($this->once())
            ->method('execute')
            ->willReturn($actionData);

        $this->handler->expects($this->once())
            ->method('createForShoppingList')
            ->with(
                $this->isInstanceOf(ShoppingList::class),
                array_values($productIds),
                $productUnitsQuantities
            )
            ->willReturn(count($data));

        $request = new Request();
        $request->setSession($this->createMock(Session::class));

        $this->processor->process($data, $request);
    }

    public function testProcessWhenActionGroupFailedWithErrors()
    {
        $data = $this->getProductData();

        $productIds = ['sku1абв' => 1, 'sku2' => 2];
        $productUnitsQuantities = ['SKU1АБВ' => ['kg' => 2], 'SKU2' => ['liter' => 3]];

        $shoppingList = new ShoppingList();

        $this->shoppingListManager->expects($this->once())
            ->method('create')
            ->willReturn($shoppingList);

        $this->dateFormatter->expects($this->once())
            ->method('format')
            ->willReturn('Mar 28, 2016, 2:50 PM');

        $this->translator->expects($this->once())
            ->method('trans')
            ->willReturn('Quick Order (Mar 28, 2016, 2:50 PM)');

        $actionData = new ActionData([
            'shoppingList' => $shoppingList,
            'redirectUrl' => null,
            'errors' => []
        ]);

        $query = $this->createMock(AbstractQuery::class);
        $query->expects($this->once())
            ->method('getArrayResult')
            ->willReturn([
                ['id' => 1, 'sku' => 'SKU1АБВ'],
                ['id' => 2, 'sku' => 'SKU2'],
            ]);
        $queryBuilder = $this->createMock(QueryBuilder::class);

        $this->productRepository->expects($this->once())
            ->method('getProductsIdsBySkuQueryBuilder')
            ->with(['sku1абв', 'sku2'])
            ->willReturn($queryBuilder);

        $this->aclHelper->expects($this->once())
            ->method('apply')
            ->with($queryBuilder)
            ->willReturn($query);

        $this->actionGroupRegistry->expects($this->once())
            ->method('findByName')
            ->with('start_shoppinglist_checkout')
            ->willReturn($this->actionGroup);

        $this->actionGroup->expects($this->once())
            ->method('execute')
            ->willReturn($actionData);

        $this->handler->expects($this->once())
            ->method('createForShoppingList')
            ->with(
                $this->isInstanceOf(ShoppingList::class),
                array_values($productIds),
                $productUnitsQuantities
            )
            ->willReturn(count($data));

        $request = new Request();
        $this->assertFailedFlashMessage($request);

        $this->em->expects($this->once())
            ->method('rollback');

        $this->assertFalse($this->processor->process($data, $request));
    }

    public function testProcessWhenHandlerThrowsException()
    {
        $data = $this->getProductData();

        $shoppingList = new ShoppingList();
        $this->shoppingListManager->expects($this->once())
            ->method('create')
            ->willReturn($shoppingList);

        $this->dateFormatter->expects($this->once())
            ->method('format')
            ->willReturn('Mar 28, 2016, 2:50 PM');

        $this->translator->expects($this->once())
            ->method('trans')
            ->willReturn('Quick Order (Mar 28, 2016, 2:50 PM)');

        $query = $this->createMock(AbstractQuery::class);
        $query->expects($this->once())
            ->method('getArrayResult')
            ->willReturn([]);
        $queryBuilder = $this->createMock(QueryBuilder::class);

        $this->productRepository->expects($this->once())
            ->method('getProductsIdsBySkuQueryBuilder')
            ->with(['sku1абв', 'sku2'])
            ->willReturn($queryBuilder);

        $this->aclHelper->expects($this->once())
            ->method('apply')
            ->with($queryBuilder)
            ->willReturn($query);

        $this->handler->expects($this->once())
            ->method('createForShoppingList')
            ->willThrowException(new AccessDeniedException());

        $request = new Request();
        $this->assertFailedFlashMessage($request);

        $this->em->expects($this->once())
            ->method('rollback');

        $this->assertEquals(null, $this->processor->process($data, $request));
    }

    public function testProcessWhenNoItemsCreatedForShoppingList()
    {
        $data = $this->getProductData();

        $productIds = ['sku1абв' => 1, 'sku2' => 2];
        $productUnitsQuantities = ['SKU1АБВ' => ['kg' => 2], 'SKU2' => ['liter' => 3]];

        $shoppingList = new ShoppingList();

        $this->shoppingListManager->expects($this->once())
            ->method('create')
            ->willReturn($shoppingList);

        $this->dateFormatter->expects($this->once())
            ->method('format')
            ->willReturn('Mar 28, 2016, 2:50 PM');

        $this->translator->expects($this->once())
            ->method('trans')
            ->willReturn('Quick Order (Mar 28, 2016, 2:50 PM)');

        $query = $this->createMock(AbstractQuery::class);
        $query->expects($this->once())
            ->method('getArrayResult')
            ->willReturn([
                ['id' => 1, 'sku' => 'SKU1АБВ'],
                ['id' => 2, 'sku' => 'SKU2'],
            ]);
        $queryBuilder = $this->createMock(QueryBuilder::class);

        $this->productRepository->expects($this->once())
            ->method('getProductsIdsBySkuQueryBuilder')
            ->with(['sku1абв', 'sku2'])
            ->willReturn($queryBuilder);

        $this->aclHelper->expects($this->once())
            ->method('apply')
            ->with($queryBuilder)
            ->willReturn($query);

        $this->handler->expects($this->once())
            ->method('createForShoppingList')
            ->with(
                $this->isInstanceOf(ShoppingList::class),
                array_values($productIds),
                $productUnitsQuantities
            )
            ->willReturn(0);

        $this->em->expects($this->once())
            ->method('rollback');

        $request = new Request();
        $this->assertFailedFlashMessage($request);

        $this->assertEquals(null, $this->processor->process($data, $request));
    }

    private function assertFailedFlashMessage(Request $request)
    {
        $message = 'failed message';

        $this->messageGenerator->expects($this->once())
            ->method('getFailedMessage')
            ->willReturn($message);

        $flashBag = $this->createMock(FlashBagInterface::class);
        $flashBag->expects($this->once())
            ->method('add')
            ->with('error')
            ->willReturn($flashBag);

        $session = $this->createMock(Session::class);
        $session->expects($this->once())
            ->method('getFlashBag')
            ->willReturn($flashBag);

        $request->setSession($session);
    }

    private function getProductData(): array
    {
        return [
            ProductDataStorage::ENTITY_ITEMS_DATA_KEY => [
                ['productSku' => 'sku1абв', 'productQuantity' => 2, 'productUnit' => 'kg'],
                ['productSku' => 'sku2', 'productQuantity' => 3, 'productUnit' => 'liter'],
            ]
        ];
    }
}
