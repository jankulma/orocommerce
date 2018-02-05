<?php

namespace Oro\Bundle\PricingBundle\Model;

use Doctrine\Bundle\DoctrineBundle\Registry;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Oro\Bundle\PricingBundle\Entity\CombinedPriceList;
use Oro\Bundle\PricingBundle\Entity\CombinedProductPrice;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\WebsiteBundle\Entity\Website;
use Oro\Bundle\WebsiteSearchBundle\Event\ReindexationRequestEvent;

class CombinedPriceListTriggerHandler
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var bool
     */
    protected $isSessionStarted;

    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    /**
     * @var array
     */
    protected $scheduleCpl = [];

    /**
     * @var array
     */
    protected $productsSchedule = [];

    /**
     * CombinedPriceListTriggerHandler constructor.
     * @param Registry $registry
     * @param EventDispatcherInterface $eventDispatcher
     */
    public function __construct(Registry $registry, EventDispatcherInterface $eventDispatcher)
    {
        $this->registry = $registry;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @param CombinedPriceList $combinedPriceList
     * @param Website $website
     */
    public function process(CombinedPriceList $combinedPriceList, Website $website = null)
    {
        $websiteId = $website ? $website->getId() : null;
        $this->scheduleCpl[$websiteId][$combinedPriceList->getId()] = $combinedPriceList->getId();

        if (!$this->isSessionStarted) {
            $this->send();
        }
    }

    /**
     * @param CombinedPriceList $combinedPriceList
     * @param array|Product[] $products
     * @param Website|null $website
     */
    public function processByProduct(
        CombinedPriceList $combinedPriceList,
        array $products = [],
        Website $website = null
    ) {
        if ($products) {
            $websiteId = $website ? $website->getId() : null;
            foreach ($products as $productId) {
                $this->productsSchedule[$websiteId][$productId] = $productId;
            }
            if (!$this->isSessionStarted) {
                $this->send();
            }
        } else {
            $this->process($combinedPriceList, $website);
        }
    }

    /**
     * @param array $combinedPriceLists
     * @param Website|null $website
     */
    public function massProcess(array $combinedPriceLists, Website $website = null)
    {
        $websiteId = $website ? $website->getId() : null;

        $repository = $this->registry->getManagerForClass(CombinedProductPrice::class)
            ->getRepository(CombinedProductPrice::class);
        $productIds = $repository->getProductIdsByPriceLists($combinedPriceLists);
        foreach ($productIds as $productId) {
            $this->productsSchedule[$websiteId][$productId] = $productId;
        }

        if (!$this->isSessionStarted) {
            $this->send();
        }
    }

    public function startCollect()
    {
        $this->isSessionStarted = true;
    }

    public function rollback()
    {
        $this->scheduleCpl = [];
        $this->productsSchedule = [];
        $this->isSessionStarted = false;
    }

    public function commit()
    {
        $this->isSessionStarted = false;
        $this->send();
    }

    protected function send()
    {
        foreach ($this->scheduleCpl as $websiteId => $cplIds) {
            $websiteIds = $websiteId ? [$websiteId] : [];
            $this->dispatchByPriceLists($websiteIds, $cplIds);
        }

        foreach ($this->productsSchedule as $websiteId => $productIds) {
            $websiteIds = $websiteId ? [$websiteId] : [];
            $event = new ReindexationRequestEvent([Product::class], $websiteIds, array_values($productIds));
            $this->eventDispatcher->dispatch(ReindexationRequestEvent::EVENT_NAME, $event);
        }

        $this->scheduleCpl = [];
        $this->productsSchedule = [];
    }

    /**
     * @param array $websiteIds
     * @param array $cplIds
     */
    protected function dispatchByPriceLists(array $websiteIds, array $cplIds)
    {
        // use minimal product prices because of table size
        $repository = $this->registry->getManagerForClass(CombinedProductPrice::class)
            ->getRepository(CombinedProductPrice::class);
        $productIds = $repository->getProductIdsByPriceLists($cplIds);
        foreach ($productIds as $productId) {
            if (!$websiteIds) {
                $this->productsSchedule[null][$productId] = $productId;
            }

            foreach ($websiteIds as $websiteId) {
                $this->productsSchedule[$websiteId][$productId] = $productId;
            }
        }
    }
}
