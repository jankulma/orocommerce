<?php

namespace Oro\Bundle\TaxBundle\Tests\Unit\EventListener;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\TaxBundle\Entity\TaxValue;
use Oro\Bundle\TaxBundle\EventListener\TaxValueListener;
use Oro\Bundle\TaxBundle\Manager\TaxValueManager;

class TaxValueListenerTest extends \PHPUnit\Framework\TestCase
{
    /** @var TaxValueManager|\PHPUnit\Framework\MockObject\MockObject */
    protected $taxValueManager;

    /** @var TaxValueListener */
    protected $listener;

    protected function setUp()
    {
        $this->taxValueManager = $this->getMockBuilder('Oro\Bundle\TaxBundle\Manager\TaxValueManager')
            ->disableOriginalConstructor()
            ->getMock();

        $this->listener = new TaxValueListener($this->taxValueManager);
    }

    protected function tearDown()
    {
        unset($this->listener, $this->taxValueManager);
    }

    public function testPostRemove()
    {
        $this->taxValueManager->expects($this->once())
            ->method('clear');

        $taxValue = new TaxValue();

        /** @var ObjectManager $objectManager */
        $objectManager = $this->getMockBuilder('Doctrine\Common\Persistence\ObjectManager')
            ->disableOriginalConstructor()
            ->getMock();

        $event = new LifecycleEventArgs($taxValue, $objectManager);

        $this->listener->postRemove($taxValue, $event);
    }
}
