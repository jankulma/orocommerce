<?php

namespace Oro\Bundle\PaymentBundle\Tests\Unit\Event;

use Oro\Bundle\PaymentBundle\Event\RequirePaymentRedirectEvent;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;

class RequirePaymentRedirectEventTest extends \PHPUnit\Framework\TestCase
{
    /** @var PaymentMethodInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $paymentMethod;

    /** @var RequirePaymentRedirectEvent */
    private $event;

    protected function setUp()
    {
        $this->paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $this->event = new RequirePaymentRedirectEvent($this->paymentMethod);
    }

    protected function tearDown()
    {
        unset($this->paymentMethod, $this->event);
    }

    public function testIsRedirectNeeded()
    {
        $this->assertFalse($this->event->isRedirectRequired());
        $this->event->setRedirectRequired(true);
        $this->assertTrue($this->event->isRedirectRequired());
    }

    public function testGetPaymentMethod()
    {
        $this->assertInstanceOf(PaymentMethodInterface::class, $this->event->getPaymentMethod());
    }
}
