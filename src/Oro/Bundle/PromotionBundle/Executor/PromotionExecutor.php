<?php

namespace Oro\Bundle\PromotionBundle\Executor;

use Doctrine\Common\Cache\Cache;
use Oro\Bundle\PromotionBundle\Discount\Converter\DiscountContextConverterInterface;
use Oro\Bundle\PromotionBundle\Discount\DiscountContextInterface;
use Oro\Bundle\PromotionBundle\Discount\Strategy\StrategyProvider;
use Oro\Bundle\PromotionBundle\Provider\PromotionDiscountsProviderInterface;

/**
 * This class fills context with discounts' information for a given source entity using currently selected strategy.
 */
class PromotionExecutor
{
    /**
     * @var DiscountContextConverterInterface
     */
    private $discountContextConverter;

    /**
     * @var StrategyProvider
     */
    private $discountStrategyProvider;

    /**
     * @var PromotionDiscountsProviderInterface
     */
    private $promotionDiscountsProvider;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @param DiscountContextConverterInterface $discountContextConverter
     * @param StrategyProvider $discountStrategyProvider
     * @param PromotionDiscountsProviderInterface $promotionDiscountsProvider
     * @param Cache $cache
     */
    public function __construct(
        DiscountContextConverterInterface $discountContextConverter,
        StrategyProvider $discountStrategyProvider,
        PromotionDiscountsProviderInterface $promotionDiscountsProvider,
        Cache $cache
    ) {
        $this->discountContextConverter = $discountContextConverter;
        $this->discountStrategyProvider = $discountStrategyProvider;
        $this->promotionDiscountsProvider = $promotionDiscountsProvider;
        $this->cache = $cache;
    }

    /**
     * @param object $sourceEntity
     * @return DiscountContextInterface
     */
    public function execute($sourceEntity): DiscountContextInterface
    {
        $cacheKey = $this->getCacheKey($sourceEntity);
        if ($this->cache->contains($cacheKey)) {
            return $this->cache->fetch($cacheKey);
        }

        $discountContext = $this->discountContextConverter->convert($sourceEntity);
        $discounts = $this->promotionDiscountsProvider->getDiscounts($sourceEntity, $discountContext);

        if ($discounts) {
            $strategy = $this->discountStrategyProvider->getActiveStrategy();
            $discountContext = $strategy->process($discountContext, $discounts);
        }

        $this->cache->save($cacheKey, $discountContext);

        return $discountContext;
    }

    /**
     * @param object $sourceEntity
     * @return bool
     */
    public function supports($sourceEntity)
    {
        return $this->discountContextConverter->supports($sourceEntity);
    }

    /**
     * @param object $sourceEntity
     * @return string
     */
    private function getCacheKey($sourceEntity)
    {
        return md5(serialize($sourceEntity));
    }
}
