<?php

namespace Oro\Bundle\VisibilityBundle\Tests\Functional\Entity\Visibility\Repository;

use Oro\Bundle\CatalogBundle\Tests\Functional\DataFixtures\LoadCategoryData;
use Oro\Bundle\VisibilityBundle\Entity\Visibility\CustomerProductVisibility;
use Oro\Bundle\VisibilityBundle\Entity\Visibility\Repository\CustomerProductVisibilityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

class CustomerProductVisibilityRepositoryTest extends AbstractProductVisibilityRepositoryTestCase
{
    /** @var CustomerProductVisibilityRepository */
    protected $repository;

    /** @var  RegistryInterface */
    protected $registry;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->initClient();
        $this->client->useHashNavigation(true);
        $this->registry = $this->getContainer()->get('doctrine');
        $this->repository = $this->registry->getRepository(
            'OroVisibilityBundle:Visibility\CustomerProductVisibility'
        );

        $this->repository = $this->getContainer()
            ->get('doctrine')
            ->getRepository('OroVisibilityBundle:Visibility\CustomerProductVisibility');

        $this->loadFixtures(
            [
                'Oro\Bundle\VisibilityBundle\Tests\Functional\DataFixtures\LoadProductVisibilityData',
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function setToDefaultWithoutCategoryDataProvider()
    {
        return [
            [
                'category' => LoadCategoryData::FOURTH_LEVEL2,
                'deletedCategoryProducts' => ['product-8'],
            ],
        ];
    }
}
