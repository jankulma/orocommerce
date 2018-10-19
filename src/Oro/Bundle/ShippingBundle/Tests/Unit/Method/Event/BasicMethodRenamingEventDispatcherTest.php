<?php

namespace Oro\Bundle\ShippingBundle\Tests\Unit\Method\Event;

use Oro\Bundle\ShippingBundle\Method\Event\BasicMethodRenamingEventDispatcher;
use Oro\Bundle\ShippingBundle\Method\Event\MethodRenamingEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class BasicMethodRenamingEventDispatcherTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var EventDispatcherInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $eventDispatcher;

    /**
     * @var BasicMethodRenamingEventDispatcher
     */
    private $dispatcher;

    protected function setUp()
    {
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);

        $this->dispatcher = new BasicMethodRenamingEventDispatcher($this->eventDispatcher);
    }

    public function testDispatch()
    {
        $oldId = 'old_id';
        $newId = 'new_id';
        $this->eventDispatcher->expects($this->once())
            ->method('dispatch')
            ->with(MethodRenamingEvent::NAME, new MethodRenamingEvent($oldId, $newId));

        $this->dispatcher->dispatch($oldId, $newId);
    }
}
