<?php

namespace Oro\Bundle\PricingBundle\EventListener;

use Doctrine\Common\Persistence\ObjectManager;
use Oro\Bundle\MigrationBundle\Event\MigrationDataFixturesEvent;
use Oro\Bundle\PlatformBundle\EventListener\AbstractDemoDataFixturesListener;
use Oro\Bundle\PlatformBundle\Manager\OptionalListenerManager;
use Oro\Bundle\PricingBundle\Builder\CombinedPriceListsBuilder;
use Oro\Bundle\PricingBundle\Builder\PriceListProductAssignmentBuilder;
use Oro\Bundle\PricingBundle\Builder\ProductPriceBuilder;
use Oro\Bundle\PricingBundle\Entity\PriceList;

/**
 * Building all combined price lists during loading of demo data
 * Disables search re-indexation for building combined price lists
 */
class BuildPricesDemoDataFixturesListener extends AbstractDemoDataFixturesListener
{
    /** @var CombinedPriceListsBuilder */
    protected $priceListBuilder;

    /** @var ProductPriceBuilder */
    protected $priceBuilder;

    /** @var PriceListProductAssignmentBuilder */
    protected $assignmentBuilder;

    /**
     * @param OptionalListenerManager $listenerManager
     * @param CombinedPriceListsBuilder $priceListBuilder
     * @param ProductPriceBuilder $priceBuilder
     * @param PriceListProductAssignmentBuilder $assignmentBuilder
     */
    public function __construct(
        OptionalListenerManager $listenerManager,
        CombinedPriceListsBuilder $priceListBuilder,
        ProductPriceBuilder $priceBuilder,
        PriceListProductAssignmentBuilder $assignmentBuilder
    ) {
        parent::__construct($listenerManager);

        $this->priceListBuilder = $priceListBuilder;
        $this->priceBuilder = $priceBuilder;
        $this->assignmentBuilder = $assignmentBuilder;
    }

    /**
     * {@inheritDoc}
     */
    protected function afterEnableListeners(MigrationDataFixturesEvent $event)
    {
        $event->log('building all combined price lists');

        // website search index should not be re-indexed while cpl build
        $this->listenerManager->disableListener('oro_website_search.reindex_request.listener');
        $this->buildPrices($event->getObjectManager());
        $this->listenerManager->enableListener('oro_website_search.reindex_request.listener');
    }

    /**
     * @param ObjectManager $manager
     */
    protected function buildPrices(ObjectManager $manager)
    {
        $priceLists = $manager->getRepository(PriceList::class)->getPriceListsWithRules();

        foreach ($priceLists as $priceList) {
            $this->assignmentBuilder->buildByPriceList($priceList);
            $this->priceBuilder->buildByPriceList($priceList);
        }

        $now = new \DateTime();
        $this->priceListBuilder->build($now->getTimestamp());
    }
}
