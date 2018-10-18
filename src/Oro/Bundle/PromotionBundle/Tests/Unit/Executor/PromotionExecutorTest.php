<?php

namespace Oro\Bundle\PromotionBundle\Tests\Unit\Executor;

use Doctrine\Common\Cache\Cache;
use Oro\Bundle\PromotionBundle\Discount\Converter\DiscountContextConverterInterface;
use Oro\Bundle\PromotionBundle\Discount\DiscountContext;
use Oro\Bundle\PromotionBundle\Discount\DiscountInterface;
use Oro\Bundle\PromotionBundle\Discount\Strategy\StrategyInterface;
use Oro\Bundle\PromotionBundle\Discount\Strategy\StrategyProvider;
use Oro\Bundle\PromotionBundle\Executor\PromotionExecutor;
use Oro\Bundle\PromotionBundle\Provider\PromotionDiscountsProviderInterface;

class PromotionExecutorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var DiscountContextConverterInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $discountContextConverter;

    /**
     * @var StrategyProvider|\PHPUnit\Framework\MockObject\MockObject
     */
    private $discountStrategyProvider;

    /**
     * @var PromotionDiscountsProviderInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $promotionDiscountsProvider;

    /**
     * @var Cache|\PHPUnit\Framework\MockObject\MockObject
     */
    private $cache;

    /**
     * @var PromotionExecutor
     */
    private $executor;

    protected function setUp()
    {
        $this->discountContextConverter = $this->createMock(DiscountContextConverterInterface::class);
        $this->discountStrategyProvider = $this->createMock(StrategyProvider::class);
        $this->promotionDiscountsProvider = $this->createMock(PromotionDiscountsProviderInterface::class);
        $this->cache = $this->createMock(Cache::class);

        $this->executor = new PromotionExecutor(
            $this->discountContextConverter,
            $this->discountStrategyProvider,
            $this->promotionDiscountsProvider,
            $this->cache
        );
    }

    public function testExecuteNoDiscounts()
    {
        $sourceEntity = new \stdClass();
        $discountContext = new DiscountContext();

        $this->discountContextConverter->expects($this->once())
            ->method('convert')
            ->with($sourceEntity)
            ->willReturn($discountContext);

        $this->promotionDiscountsProvider->expects($this->once())
            ->method('getDiscounts')
            ->with($sourceEntity, $discountContext)
            ->willReturn([]);

        $this->discountStrategyProvider->expects($this->never())
            ->method($this->anything());

        $this->assertSame($discountContext, $this->executor->execute($sourceEntity));
    }

    public function testExecute()
    {
        $sourceEntity = new \stdClass();
        $discountContext = new DiscountContext();

        $cacheKey = md5(serialize($sourceEntity));
        $this->cache->expects($this->once())
            ->method('contains')
            ->with($cacheKey)
            ->willReturn(false);
        $this->cache->expects($this->never())
            ->method('fetch')
            ->with($cacheKey);
        $this->cache->expects($this->once())
            ->method('save')
            ->with($cacheKey, $this->isInstanceOf(DiscountContext::class));

        $this->discountContextConverter->expects($this->once())
            ->method('convert')
            ->with($sourceEntity)
            ->willReturn($discountContext);

        $discounts = [$this->createMock(DiscountInterface::class), $this->createMock(DiscountInterface::class)];

        $this->promotionDiscountsProvider->expects($this->once())
            ->method('getDiscounts')
            ->with($sourceEntity, $discountContext)
            ->willReturn($discounts);

        $strategy = $this->createMock(StrategyInterface::class);
        $this->discountStrategyProvider->expects($this->once())
            ->method('getActiveStrategy')
            ->willReturn($strategy);

        $modifiedContext = new DiscountContext();
        $strategy->expects($this->once())
            ->method('process')
            ->with($discountContext, $discounts)
            ->willReturn($modifiedContext);

        $this->assertSame($modifiedContext, $this->executor->execute($sourceEntity));
    }

    public function testExecuteWithCache()
    {
        $sourceEntity = new \stdClass();
        $discountContext = new DiscountContext();

        $cacheKey = md5(serialize($sourceEntity));
        $this->cache->expects($this->once())
            ->method('contains')
            ->with($cacheKey)
            ->willReturn(true);
        $this->cache->expects($this->once())
            ->method('fetch')
            ->with($cacheKey)
            ->willReturn($discountContext);
        $this->cache->expects($this->never())
            ->method('save')
            ->with($cacheKey, $this->isInstanceOf(DiscountContext::class));

        $this->discountContextConverter->expects($this->never())
            ->method('convert');
        $this->promotionDiscountsProvider->expects($this->never())
            ->method('getDiscounts');
        $this->discountStrategyProvider->expects($this->never())
            ->method('getActiveStrategy');

        $this->assertSame($discountContext, $this->executor->execute($sourceEntity));
    }

    /**
     * @dataProvider trueFalseDataProvider
     * @param bool $result
     */
    public function testSupports($result)
    {
        $entity = new \stdClass();
        $this->discountContextConverter->expects($this->once())
            ->method('supports')
            ->willReturn($result);

        $this->assertSame($result, $this->executor->supports($entity));
    }

    /**
     * @return array
     */
    public function trueFalseDataProvider()
    {
        return [
            [true],
            [false]
        ];
    }
}
