<?php

namespace Oro\Bundle\VisibilityBundle\Tests\Unit\Entity\EntityListener;

use Oro\Bundle\VisibilityBundle\Entity\EntityListener\ProductVisibilityListener;
use Oro\Bundle\VisibilityBundle\Entity\Visibility\VisibilityInterface;
use Oro\Bundle\VisibilityBundle\Model\VisibilityMessageHandler;
use Oro\Component\Testing\Unit\EntityTrait;

class ProductVisibilityListenerTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /**
     * @var VisibilityMessageHandler|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $visibilityChangeMessageHandler;

    /**
     * @var ProductVisibilityListener
     */
    protected $visibilityListener;

    protected function setUp()
    {
        $this->visibilityChangeMessageHandler = $this->getMockBuilder(VisibilityMessageHandler::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->visibilityListener = new ProductVisibilityListener($this->visibilityChangeMessageHandler);
        $this->visibilityListener->setTopic('oro_visibility.visibility.resolve_product_visibility');
    }

    public function testPostPersist()
    {
        /** @var VisibilityInterface|\PHPUnit\Framework\MockObject\MockObject $visibility * */
        $visibility = $this->createMock(VisibilityInterface::class);

        $this->visibilityChangeMessageHandler->expects($this->once())
            ->method('addMessageToSchedule')
            ->with('oro_visibility.visibility.resolve_product_visibility', $visibility);
        $this->visibilityListener->postPersist($visibility);
    }

    public function testPreUpdate()
    {
        /** @var VisibilityInterface|\PHPUnit\Framework\MockObject\MockObject $visibility * */
        $visibility = $this->createMock(VisibilityInterface::class);

        $this->visibilityChangeMessageHandler->expects($this->once())
            ->method('addMessageToSchedule')
            ->with('oro_visibility.visibility.resolve_product_visibility', $visibility);

        $this->visibilityListener->preUpdate($visibility);
    }

    public function testPreRemove()
    {
        /** @var VisibilityInterface|\PHPUnit\Framework\MockObject\MockObject $visibility * */
        $visibility = $this->createMock(VisibilityInterface::class);

        $this->visibilityChangeMessageHandler->expects($this->once())
            ->method('addMessageToSchedule')
            ->with('oro_visibility.visibility.resolve_product_visibility', $visibility);

        $this->visibilityListener->preRemove($visibility);
    }
}
