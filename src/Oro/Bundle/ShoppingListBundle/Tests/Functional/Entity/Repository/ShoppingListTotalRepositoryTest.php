<?php

namespace Oro\Bundle\ShoppingListBundle\Tests\Functional\Entity\Repository;

use Oro\Bundle\EntityBundle\ORM\Registry;
use Oro\Bundle\PricingBundle\SubtotalProcessor\Model\Subtotal;
use Oro\Bundle\PricingBundle\Tests\Functional\DataFixtures\LoadCombinedProductPrices;
use Oro\Bundle\ShoppingListBundle\Entity\Repository\ShoppingListTotalRepository;
use Oro\Bundle\ShoppingListBundle\Entity\ShoppingList;
use Oro\Bundle\ShoppingListBundle\Entity\ShoppingListTotal;
use Oro\Bundle\ShoppingListBundle\Tests\Functional\DataFixtures\LoadShoppingListLineItems;
use Oro\Bundle\ShoppingListBundle\Tests\Functional\DataFixtures\LoadShoppingLists;
use Oro\Bundle\TestFrameworkBundle\Test\WebTestCase;

class ShoppingListTotalRepositoryTest extends WebTestCase
{
    /**
     * @var Registry
     */
    protected $registry;

    protected function setUp()
    {
        $this->initClient([], $this->generateBasicAuthHeader());
        $this->client->useHashNavigation(true);

        $this->registry = $this->getContainer()->get('doctrine');
        $this->loadFixtures(
            [
                LoadShoppingListLineItems::class,
                LoadCombinedProductPrices::class,
            ]
        );
    }
    
    public function testInvalidateByCombinedPriceList()
    {
        /** @var ShoppingList $shoppingList */
        $shoppingList = $this->getReference(LoadShoppingLists::SHOPPING_LIST_3);
        $invalidTotal = new ShoppingListTotal($shoppingList, 'USD');
        $subtotal = (new Subtotal())->setCurrency('USD')->setAmount(1);
        $invalidTotal->setValid(true);
        $invalidTotal->setSubtotal($subtotal);

        $manager = $this->registry->getManagerForClass('OroShoppingListBundle:ShoppingListTotal');
        $manager->persist($invalidTotal);
        $manager->flush();

        $cpl = $this->getReference('1f');
        $manager->getRepository('OroShoppingListBundle:ShoppingListTotal')
            ->invalidateByCombinedPriceList([$cpl->getId()]);

        $manager->refresh($invalidTotal);
        $this->assertFalse($invalidTotal->isValid());
    }

    public function testInvalidateByCustomerUsers()
    {
        $manager = $this->registry->getManagerForClass('OroShoppingListBundle:ShoppingListTotal');
        /** @var ShoppingListTotalRepository $repository */
        $repository = $manager->getRepository('OroShoppingListBundle:ShoppingListTotal');

        $shoppingList = $this->getReference(LoadShoppingLists::SHOPPING_LIST_3);
        $shoppingListTotal = $repository->findOneBy(['shoppingList' => $shoppingList->getId()]);
        $shoppingListTotal->setValid(true);
        $manager->flush();

        $repository->invalidateByCustomers(
            [$shoppingListTotal->getShoppingList()->getCustomer()->getId()],
            $shoppingListTotal->getShoppingList()->getWebsite()
        );

        $manager->refresh($shoppingListTotal);
        $this->assertFalse($shoppingListTotal->isValid());
    }
}
