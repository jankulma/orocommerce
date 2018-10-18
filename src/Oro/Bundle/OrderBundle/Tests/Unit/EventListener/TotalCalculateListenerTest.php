<?php

namespace Oro\Bundle\OrderBundle\Tests\Unit\EventListener;

use Oro\Bundle\ActionBundle\Provider\CurrentApplicationProviderInterface;
use Oro\Bundle\FrontendBundle\Provider\ActionCurrentApplicationProvider;
use Oro\Bundle\OrderBundle\Entity\Order;
use Oro\Bundle\OrderBundle\EventListener\TotalCalculateListener;
use Oro\Bundle\OrderBundle\Form\Type\OrderType;
use Oro\Bundle\PricingBundle\Event\TotalCalculateBeforeEvent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormRegistryInterface;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\Request;

class TotalCalculateListenerTest extends \PHPUnit\Framework\TestCase
{
    const FORM_DATA = ['field' => 'value'];

    /**
     * @var FormFactory|\PHPUnit\Framework\MockObject\MockObject
     */
    private $formFactory;

    /**
     * @var CurrentApplicationProviderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $applicationProvider;

    /**
     * @var FormRegistryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $formRegistry;

    /**
     * @var TotalCalculateListener
     */
    private $listener;

    protected function setUp()
    {
        $this->formFactory = $this->createMock(FormFactory::class);
        $this->applicationProvider = $this->createMock(CurrentApplicationProviderInterface::class);
        $this->formRegistry = $this->createMock(FormRegistryInterface::class);
        $this->listener = new TotalCalculateListener(
            $this->formFactory,
            $this->applicationProvider,
            $this->formRegistry
        );
    }

    public function testOnBeforeTotalCalculateWhenEntityIsNotOrder()
    {
        $this->applicationProvider->expects($this->never())
            ->method('getCurrentApplication');
        $this->formFactory->expects($this->never())
            ->method('create');

        $event = new TotalCalculateBeforeEvent(new \stdClass(), $this->getRequest());
        $this->listener->onBeforeTotalCalculate($event);
    }

    public function testOnBeforeTotalCalculateWhenFormIsNotDefined()
    {
        $this->applicationProvider->expects($this->once())
            ->method('getCurrentApplication')
            ->willReturn('some other application');
        $this->formFactory->expects($this->never())
            ->method('create');

        $event = new TotalCalculateBeforeEvent(new Order(), $this->getRequest());
        $this->listener->onBeforeTotalCalculate($event);
    }

    public function testOnBeforeTotalCalculateWhenRequestNotContainsData()
    {
        $this->applicationProvider->expects($this->once())
            ->method('getCurrentApplication')
            ->willReturn(ActionCurrentApplicationProvider::DEFAULT_APPLICATION);
        $this->formFactory->expects($this->never())
            ->method('create');

        $this->configureFormRegistry(OrderType::class, OrderType::NAME);

        $event = new TotalCalculateBeforeEvent(new Order(), $this->getRequest());
        $this->listener->onBeforeTotalCalculate($event);
    }

    public function testOnBeforeTotalCalculate()
    {
        $this->applicationProvider->expects($this->once())
            ->method('getCurrentApplication')
            ->willReturn(ActionCurrentApplicationProvider::DEFAULT_APPLICATION);

        $entity = new Order();
        $request = $this->getRequest([OrderType::NAME => ['some data'], 'formName' => self::FORM_DATA]);
        $form = $this->createMock(FormInterface::class);
        $form->expects($this->any())
            ->method('getName')
            ->willReturn('formName');
        $form->expects($this->once())
            ->method('submit')
            ->with(self::FORM_DATA);
        $this->formFactory->expects($this->once())
            ->method('create')
            ->with(OrderType::class, $entity)
            ->willReturn($form);

        $this->configureFormRegistry(OrderType::class, OrderType::NAME);

        $event = new TotalCalculateBeforeEvent($entity, $request);
        $this->listener->onBeforeTotalCalculate($event);
    }

    /**
     * @param string $className
     * @param string $formName
     */
    private function configureFormRegistry($className, $formName)
    {
        $formType = $this->createMock(FormTypeInterface::class);
        $formType
            ->expects($this->any())
            ->method('getBlockPrefix')
            ->willReturn($formName);

        $this->formRegistry
            ->expects($this->any())
            ->method('getType')
            ->with($className)
            ->willReturn($formType);
    }

    /**
     * @param array $postData
     * @return Request
     */
    private function getRequest(array $postData = [])
    {
        return new Request([], $postData);
    }
}
