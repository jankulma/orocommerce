<?php

namespace Oro\Bundle\ProductBundle\Tests\Unit\EventListener;

use Oro\Bundle\ConfigBundle\Config\ConfigManager;
use Oro\Bundle\FrontendBundle\Request\FrontendHelper;
use Oro\Bundle\ProductBundle\Event\ProductSearchQueryRestrictionEvent;
use Oro\Bundle\ProductBundle\EventListener\ProductSearchQueryRestrictionEventListener;
use Oro\Bundle\ProductBundle\Model\ProductVisibilitySearchQueryModifier;
use Oro\Bundle\SearchBundle\Query\Query;

abstract class AbstractProductSearchQueryRestrictionEventListenerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ConfigManager|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $configManager;

    /**
     * @var string
     */
    protected $frontendConfigPath = '/front/end/cfg/path';

    /**
     * @var ProductVisibilitySearchQueryModifier|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $modifier;

    /**
     * @var ProductSearchQueryRestrictionEventListener
     */
    protected $listener;

    /**
     * @var array
     */
    protected $statuses = ['in_stock'];

    /**
     * @var FrontendHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $frontendHelper;

    public function setUp()
    {
        $this->configManager = $this->getMockBuilder(ConfigManager::class)
            ->disableOriginalConstructor()->getMock();

        $this->modifier = $this->getQueryModifier();

        $this->frontendHelper = $this->getFrontendHelper();

        $this->listener = $this->createListener();
    }

    /**
     * @return FrontendHelper|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getFrontendHelper()
    {
        /** @var FrontendHelper|\PHPUnit\Framework\MockObject\MockObject $frontendHelper */
        $frontendHelper = $this->getMockBuilder(FrontendHelper::class)
            ->disableOriginalConstructor()->getMock();

        return $frontendHelper;
    }

    /**
     * @return ProductVisibilitySearchQueryModifier|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getQueryModifier()
    {
        /** @var ProductVisibilitySearchQueryModifier|\PHPUnit\Framework\MockObject\MockObject $queryModifier */
        $queryModifier = $this->getMockBuilder(ProductVisibilitySearchQueryModifier::class)->getMock();

        return $queryModifier;
    }

    protected function configureDependenciesForFrontend()
    {
        $this->frontendHelper
            ->expects($this->atLeastOnce())
            ->method('isFrontendRequest')
            ->willReturn(true);

        $this->modifier->expects($this->once())
            ->method('modifyByInventoryStatus');

        $this->configManager->expects($this->once())
            ->method('get')
            ->with($this->frontendConfigPath)
            ->willReturn($this->statuses);
    }

    protected function configureDependenciesForBackend()
    {
        $this->frontendHelper
            ->expects($this->atLeastOnce())
            ->method('isFrontendRequest')
            ->willReturn(false);

        $this->modifier->expects($this->never())
            ->method('modifyByInventoryStatus');

        $this->configManager->expects($this->never())
            ->method('get')
            ->with($this->frontendConfigPath)
            ->willReturn($this->statuses);
    }

    /**
     * @return ProductSearchQueryRestrictionEvent
     */
    protected function getEvent()
    {
        return new ProductSearchQueryRestrictionEvent(new Query());
    }

    /**
     * @return ProductSearchQueryRestrictionEventListener
     */
    abstract protected function createListener();
}
