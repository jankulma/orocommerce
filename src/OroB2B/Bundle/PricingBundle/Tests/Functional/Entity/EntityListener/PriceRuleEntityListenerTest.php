<?php

namespace OroB2B\Bundle\PricingBundle\Tests\Functional\Entity\EntityListener;

use Doctrine\ORM\EntityManagerInterface;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;
use OroB2B\Bundle\PricingBundle\Async\Topics;
use OroB2B\Bundle\PricingBundle\Entity\PriceRule;
use OroB2B\Bundle\PricingBundle\Tests\Functional\DataFixtures\LoadPriceRules;

/**
 * @dbIsolation
 */
class PriceRuleEntityListenerTest extends WebTestCase
{
    use MessageQueueTrait;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->loadFixtures([
            LoadPriceRules::class
        ]);
        $this->topic = Topics::CALCULATE_RULE;
        $this->cleanQueueMessageTraces();
    }

    public function testPreUpdate()
    {
        /** @var EntityManagerInterface $em */
        $em = $this->getContainer()->get('doctrine')->getManagerForClass(PriceRule::class);

        /** @var PriceRule $rule */
        $rule = $this->getReference(LoadPriceRules::PRICE_RULE_1);
        $rule->setRuleCondition('product.id > 42');
        $em->persist($rule);
        $em->flush();

        $traces = $this->getQueueMessageTraces();
        $this->assertCount(1, $traces);
        $this->assertEquals($rule->getPriceList()->getId(), $this->getPriceListIdFromTrace($traces[0]));
    }

    public function testPreRemove()
    {
        /** @var EntityManagerInterface $em */
        $em = $this->getContainer()->get('doctrine')->getManagerForClass(PriceRule::class);

        /** @var PriceRule $rule */
        $rule = $this->getReference(LoadPriceRules::PRICE_RULE_1);
        $em->remove($rule);
        $em->flush();

        $traces = $this->getQueueMessageTraces();
        $this->assertCount(1, $traces);
        $this->assertEquals($rule->getPriceList()->getId(), $this->getPriceListIdFromTrace($traces[0]));
    }
}
