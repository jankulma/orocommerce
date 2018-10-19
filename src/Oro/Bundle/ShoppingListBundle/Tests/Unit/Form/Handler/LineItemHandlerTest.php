<?php

namespace Oro\Bundle\ShoppingListBundle\Tests\Unit\Form\Handler;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\ProductBundle\Form\Type\FrontendLineItemType;
use Oro\Bundle\ShoppingListBundle\Entity\LineItem;
use Oro\Bundle\ShoppingListBundle\Entity\ShoppingList;
use Oro\Bundle\ShoppingListBundle\Form\Handler\LineItemHandler;
use Oro\Bundle\ShoppingListBundle\Manager\ShoppingListManager;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;

class LineItemHandlerTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    const FORM_DATA = ['field' => 'value'];

    const LINE_ITEM_SHORTCUT = 'OroShoppingListBundle:LineItem';

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|FormInterface
     */
    protected $form;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|Registry
     */
    protected $registry;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ShoppingListManager
     */
    protected $shoppingListManager;

    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|LineItem
     */
    protected $lineItem;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->form = $this->getMockBuilder('Symfony\Component\Form\FormInterface')
            ->disableOriginalConstructor()
            ->getMock();
        $this->form->expects($this->any())
            ->method('getName')
            ->willReturn(FrontendLineItemType::NAME);
        $this->request = new Request();
        $this->registry = $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')
            ->disableOriginalConstructor()
            ->getMock();
        $this->shoppingListManager =
            $this->getMockBuilder('Oro\Bundle\ShoppingListBundle\Manager\ShoppingListManager')
                ->disableOriginalConstructor()
                ->getMock();

        $this->lineItem = $this->createMock('Oro\Bundle\ShoppingListBundle\Entity\LineItem');
        $shoppingList = $this->createMock('Oro\Bundle\ShoppingListBundle\Entity\ShoppingList');

        $this->lineItem->expects($this->any())
            ->method('getShoppingList')
            ->willReturn($shoppingList);
    }

    public function testProcessWrongMethod()
    {
        $this->registry
            ->expects($this->never())
            ->method('getManagerForClass');

        $handler = new LineItemHandler(
            $this->form,
            $this->request,
            $this->registry,
            $this->shoppingListManager
        );
        $this->assertFalse($handler->process($this->lineItem));
    }

    public function testProcessFormNotValid()
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|EntityManagerInterface $manager */
        $manager = $this->createMock('Doctrine\ORM\EntityManagerInterface');
        $manager->expects($this->once())
            ->method('beginTransaction');
        $manager->expects($this->never())
            ->method('commit');
        $manager->expects($this->once())
            ->method('rollback');

        $manager->expects($this->never())
            ->method('commit');

        $this->registry->expects($this->once())
            ->method('getManagerForClass')
            ->with(self::LINE_ITEM_SHORTCUT)
            ->will($this->returnValue($manager));

        $this->request = Request::create('/', 'POST', [FrontendLineItemType::NAME => self::FORM_DATA]);

        $this->form->expects($this->once())
            ->method('submit')
            ->with(self::FORM_DATA);
        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(false));

        $handler = new LineItemHandler(
            $this->form,
            $this->request,
            $this->registry,
            $this->shoppingListManager
        );
        $this->assertFalse($handler->process($this->lineItem));
    }

    public function testProcessSuccess()
    {
        $this->request = Request::create('/', 'PUT', [FrontendLineItemType::NAME => ['shoppingListLabel' => 'label']]);

        /** @var \PHPUnit\Framework\MockObject\MockObject|EntityManagerInterface $manager */
        $manager = $this->createMock('Doctrine\ORM\EntityManagerInterface');
        $manager->expects($this->once())
            ->method('beginTransaction');
        $manager->expects($this->once())
            ->method('commit');
        $manager->expects($this->never())
            ->method('rollback');
        $manager->expects($this->once())
            ->method('commit');

        $this->registry->expects($this->once())
            ->method('getManagerForClass')
            ->with(self::LINE_ITEM_SHORTCUT)
            ->will($this->returnValue($manager));

        $this->form->expects($this->once())
            ->method('submit')
            ->with(['shoppingListLabel' => 'label', 'shoppingList' => 777]);
        $this->form->expects($this->once())
            ->method('isValid')
            ->will($this->returnValue(true));

        $shoppingList = $this->getEntity(ShoppingList::class, ['id' => 777]);
        $this->lineItem->expects($this->once())
            ->method('getShoppingList')
            ->willReturn($shoppingList);

        $this->shoppingListManager->expects($this->once())
            ->method('addLineItem')
            ->willReturn($shoppingList);

        $this->shoppingListManager->expects($this->once())
            ->method('createCurrent')
            ->willReturn($shoppingList);

        $handler = new LineItemHandler(
            $this->form,
            $this->request,
            $this->registry,
            $this->shoppingListManager
        );
        $this->assertTrue($handler->process($this->lineItem));
    }
}
