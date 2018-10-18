<?php

namespace Oro\Bundle\ShoppingListBundle\Tests\Functional\Layout\DataProvider;

use Oro\Bundle\ShoppingListBundle\Entity\ShoppingList;
use Oro\Bundle\ShoppingListBundle\Form\Type\ShoppingListType;
use Oro\Bundle\ShoppingListBundle\Layout\DataProvider\ShoppingListFormProvider;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ShoppingListFormProviderTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var ShoppingListFormProvider */
    protected $dataProvider;

    /** @var FormFactoryInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $formFactory;

    /** @var UrlGeneratorInterface|\PHPUnit\Framework\MockObject\MockObject */
    protected $router;

    protected function setUp()
    {
        $this->formFactory = $this->createMock(FormFactoryInterface::class);
        $this->router = $this->createMock(UrlGeneratorInterface::class);

        $this->dataProvider = new ShoppingListFormProvider($this->formFactory, $this->router);
    }

    public function testGetShoppingListFormViewWithoutId()
    {
        $shoppingList = $this->getEntity(ShoppingList::class);
        $action = 'form_action';

        $formView = $this->createMock(FormView::class);

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('createView')
            ->willReturn($formView);

        $this->formFactory
            ->expects($this->once())
            ->method('create')
            ->with(ShoppingListType::class, $shoppingList, ['action' => $action])
            ->willReturn($form);

        $this->router
            ->expects($this->exactly(2))
            ->method('generate')
            ->with(ShoppingListFormProvider::SHOPPING_LIST_CREATE_ROUTE_NAME, [])
            ->willReturn($action);

        // Get form without existing data in locale cache
        $result = $this->dataProvider->getShoppingListFormView($shoppingList);
        $this->assertInstanceOf(FormView::class, $result);

        // Get form with existing data in locale cache
        $result = $this->dataProvider->getShoppingListFormView($shoppingList);
        $this->assertInstanceOf(FormView::class, $result);
    }

    public function testGetShoppingListFormViewWithId()
    {
        $shoppingList = $this->getEntity(ShoppingList::class, ['id' => 2]);
        $action = 'form_action';

        $formView = $this->createMock(FormView::class);

        $form = $this->createMock(FormInterface::class);
        $form->expects($this->once())
            ->method('createView')
            ->willReturn($formView);

        $this->formFactory
            ->expects($this->once())
            ->method('create')
            ->with(ShoppingListType::class, $shoppingList, ['action' => $action])
            ->willReturn($form);

        $this->router
            ->expects($this->exactly(2))
            ->method('generate')
            ->with(ShoppingListFormProvider::SHOPPING_LIST_VIEW_ROUTE_NAME, ['id' => 2])
            ->willReturn($action);

        // Get form without existing data in locale cache
        $result = $this->dataProvider->getShoppingListFormView($shoppingList);
        $this->assertInstanceOf(FormView::class, $result);

        // Get form with existing data in locale cache
        $result = $this->dataProvider->getShoppingListFormView($shoppingList);
        $this->assertInstanceOf(FormView::class, $result);
    }
}
