<?php

namespace Oro\Bundle\InventoryBundle\Tests\Unit\EventListener;

use Oro\Bundle\CheckoutBundle\DataProvider\Manager\CheckoutLineItemsManager;
use Oro\Bundle\CheckoutBundle\Entity\Checkout;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\InventoryBundle\Entity\InventoryLevel;
use Oro\Bundle\InventoryBundle\Entity\Repository\InventoryLevelRepository;
use Oro\Bundle\InventoryBundle\EventListener\CreateOrderLineItemValidationListener;
use Oro\Bundle\InventoryBundle\Exception\InventoryLevelNotFoundException;
use Oro\Bundle\InventoryBundle\Inventory\InventoryQuantityManager;
use Oro\Bundle\OrderBundle\Entity\OrderLineItem;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ProductBundle\Entity\ProductUnit;
use Oro\Bundle\ShoppingListBundle\Event\LineItemValidateEvent;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowItem;
use Oro\Bundle\WorkflowBundle\Entity\WorkflowStep;
use Symfony\Contracts\Translation\TranslatorInterface;

class CreateOrderLineItemValidationListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var InventoryQuantityManager|\PHPUnit\Framework\MockObject\MockObject */
    private $inventoryQuantityManager;

    /** @var TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $translator;

    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrineHelper;

    /** @var CreateOrderLineItemValidationListener|\PHPUnit\Framework\MockObject\MockObject */
    private $createOrderLineItemValidationListener;

    /** @var CheckoutLineItemsManager|\PHPUnit\Framework\MockObject\MockObject */
    private $checkoutLineItemsManager;

    protected function setUp(): void
    {
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->inventoryQuantityManager = $this->createMock(InventoryQuantityManager::class);
        $this->checkoutLineItemsManager = $this->createMock(CheckoutLineItemsManager::class);

        $this->createOrderLineItemValidationListener = new CreateOrderLineItemValidationListener(
            $this->inventoryQuantityManager,
            $this->doctrineHelper,
            $this->translator,
            $this->checkoutLineItemsManager
        );
    }

    /**
     * @dataProvider onLineItemValidateProvider
     */
    public function testOnLineItemValidate(string $stepName)
    {
        $event = $this->prepareEvent($stepName);

        $inventoryLevel = $this->createMock(InventoryLevel::class);
        $inventoryLevelRepository = $this->createMock(InventoryLevelRepository::class);
        $inventoryLevelRepository->expects($this->once())
            ->method('getLevelByProductAndProductUnit')
            ->willReturn($inventoryLevel);
        $this->doctrineHelper->expects($this->once())
            ->method('getEntityRepository')
            ->with(InventoryLevel::class)
            ->willReturn($inventoryLevelRepository);
        $this->inventoryQuantityManager->expects($this->once())
            ->method('hasEnoughQuantity')
            ->willReturn(true);
        $event->expects($this->never())
            ->method('addErrorByUnit');
        $this->inventoryQuantityManager->expects($this->once())
            ->method('shouldDecrement')
            ->willReturn(true);

        $this->createOrderLineItemValidationListener->onLineItemValidate($event);
    }

    public function onLineItemValidateProvider(): array
    {
        return [
            [
                'step' => 'order_review',
            ],
            [
                'step' => 'checkout',
            ],
            [
                'step' => 'request_approval',
            ],
            [
                'step' => 'approve_request',
            ],
        ];
    }

    public function testWrongContext()
    {
        $event = $this->createMock(LineItemValidateEvent::class);
        $event->expects($this->once())
            ->method('getContext')
            ->willReturn(null);
        $event->expects($this->never())
            ->method('addErrorByUnit');
        $this->doctrineHelper->expects($this->never())
            ->method('getEntityRepository');
        $this->inventoryQuantityManager->expects($this->never())
            ->method('shouldDecrement');

        $this->createOrderLineItemValidationListener->onLineItemValidate($event);
    }

    public function testNoInventoryForBeforeCreate()
    {
        $this->expectException(InventoryLevelNotFoundException::class);

        $event = $this->prepareEvent();
        $inventoryLevelRepository = $this->createMock(InventoryLevelRepository::class);
        $this->doctrineHelper->expects($this->once())
            ->method('getEntityRepository')
            ->with(InventoryLevel::class)
            ->willReturn($inventoryLevelRepository);
        $inventoryLevelRepository->expects($this->once())
            ->method('getLevelByProductAndProductUnit')
            ->willReturn(null);
        $this->inventoryQuantityManager->expects($this->once())
            ->method('shouldDecrement')
            ->willReturn(true);

        $this->createOrderLineItemValidationListener->onLineItemValidate($event);
    }

    private function prepareEvent(
        string $stepName = 'order_review'
    ): LineItemValidateEvent|\PHPUnit\Framework\MockObject\MockObject {
        $event = $this->createMock(LineItemValidateEvent::class);

        $numberOfItems = 5;
        $product = $this->createMock(Product::class);
        $productUnit = $this->createMock(ProductUnit::class);

        $lineItem = $this->createMock(OrderLineItem::class);
        $lineItem->expects($this->any())->method('getProduct')->willReturn($product);
        $lineItem->expects($this->once())->method('getProductUnit')->willReturn($productUnit);
        $lineItem->expects($this->any())->method('getQuantity')->willReturn($numberOfItems);

        $this->checkoutLineItemsManager->expects($this->once())->method('getData')->willReturn([$lineItem]);

        $checkout = $this->createMock(Checkout::class);

        $workflowItem = $this->createMock(WorkflowItem::class);
        $workflowItem->expects($this->any())->method('getEntity')->willReturn($checkout);

        $workflowStep = $this->createMock(WorkflowStep::class);
        $workflowStep->expects($this->once())->method('getName')->willReturn($stepName);

        $workflowItem->expects($this->once())->method('getCurrentStep')->willReturn($workflowStep);

        $event->expects($this->any())->method('getContext')->willReturn($workflowItem);

        return $event;
    }
}
