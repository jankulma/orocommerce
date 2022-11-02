<?php

namespace Oro\Bundle\ShippingBundle\Tests\Functional\Api\RestJsonApi;

use Oro\Bundle\ApiBundle\Tests\Functional\RestJsonApiTestCase;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductData;
use Oro\Bundle\ProductBundle\Tests\Functional\DataFixtures\LoadProductUnitPrecisions;
use Oro\Bundle\ShippingBundle\Entity\ProductShippingOptions;
use Oro\Bundle\ShippingBundle\Tests\Functional\DataFixtures\LoadProductShippingOptions;

/**
 * @dbIsolationPerTest
 */
class ProductTest extends RestJsonApiTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtures([LoadProductUnitPrecisions::class, LoadProductShippingOptions::class]);
    }

    private function findProductShippingOptionsByProduct(Product $product): ?ProductShippingOptions
    {
        return $this->getEntityManager()
            ->getRepository(ProductShippingOptions::class)
            ->findOneBy(['product' => $product]);
    }

    public function testGetShouldReturnShippingOptions()
    {
        /** @var Product $product */
        $product = $this->getReference(LoadProductData::PRODUCT_1);

        $response = $this->get(
            ['entity' => 'products', 'id' => $product->getId()]
        );

        $this->assertResponseContains(
            [
                'data' => [
                    'type'          => 'products',
                    'id'            => '<toString(@product-1->id)>',
                    'relationships' => [
                        'productShippingOptions' => [
                            'data' => [
                                [
                                    'type' => 'productshippingoptions',
                                    'id'   => '<toString(@product_shipping_options.1->id)>'
                                ],
                                [
                                    'type' => 'productshippingoptions',
                                    'id'   => '<toString(@product_shipping_options.2->id)>'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $response
        );
    }

    public function testGetListShouldReturnShippingOptions()
    {
        $response = $this->cget(
            ['entity' => 'products'],
            ['filter' => ['sku' => '@product-1->sku']]
        );

        $this->assertResponseContains(
            [
                'data' => [
                    [
                        'type'          => 'products',
                        'id'            => '<toString(@product-1->id)>',
                        'relationships' => [
                            'productShippingOptions' => [
                                'data' => [
                                    [
                                        'type' => 'productshippingoptions',
                                        'id'   => '<toString(@product_shipping_options.1->id)>'
                                    ],
                                    [
                                        'type' => 'productshippingoptions',
                                        'id'   => '<toString(@product_shipping_options.2->id)>'
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $response
        );
    }

    public function testUpdateShouldChangeProductShippingOptions()
    {
        /** @var Product $product */
        $product = $this->getReference(LoadProductData::PRODUCT_2);
        /** @var ProductShippingOptions $shippingOption */
        $shippingOption = $this->getReference(LoadProductShippingOptions::PRODUCT_SHIPPING_OPTIONS_1);

        $response = $this->patch(
            ['entity' => 'products', 'id' => (string)$product->getId()],
            [
                'data' => [
                    'type'          => 'products',
                    'id'            => (string)$product->getId(),
                    'relationships' => [
                        'productShippingOptions' => [
                            'data' => [
                                [
                                    'type' => 'productshippingoptions',
                                    'id'   => (string)$shippingOption->getId()
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        );

        $this->assertResponseContains(
            [
                'data' => [
                    'type'          => 'products',
                    'id'            => '<toString(@product-2->id)>',
                    'relationships' => [
                        'productShippingOptions' => [
                            'data' => [
                                [
                                    'type' => 'productshippingoptions',
                                    'id'   => '<toString(@product_shipping_options.1->id)>'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $response
        );

        $updatedShippingOptions = $this->findProductShippingOptionsByProduct($product);
        self::assertEquals($shippingOption->getId(), $updatedShippingOptions->getId());
    }

    public function testUpdateShouldSetProductShippingOptionsToNull()
    {
        /** @var Product $product */
        $product = $this->getReference(LoadProductData::PRODUCT_2);

        $response = $this->patch(
            ['entity' => 'products', 'id' => (string)$product->getId()],
            [
                'data' => [
                    'type'          => 'products',
                    'id'            => (string)$product->getId(),
                    'relationships' => [
                        'productShippingOptions' => [
                            'data' => null
                        ]
                    ]
                ]
            ]
        );

        $this->assertResponseContains(
            [
                'data' => [
                    'type'          => 'products',
                    'id'            => '<toString(@product-2->id)>',
                    'relationships' => [
                        'productShippingOptions' => [
                            'data' => []
                        ]
                    ]
                ]
            ],
            $response
        );

        self::assertNull($this->findProductShippingOptionsByProduct($product));
    }

    public function testCreateShouldSetShippingOptionsForNewProduct()
    {
        $response = $this->post(
            ['entity' => 'products'],
            'product/product_create.yml'
        );

        $this->assertResponseContains(
            [
                'data' => [
                    'type'          => 'products',
                    'relationships' => [
                        'productShippingOptions' => [
                            'data' => [
                                [
                                    'type' => 'productshippingoptions',
                                    'id'   => '<toString(@product_shipping_options.1->id)>'
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $response
        );

        $product = $this->getEntityManager()->getReference(Product::class, $this->getResourceId($response));
        $shippingOptions = $this->findProductShippingOptionsByProduct($product);
        self::assertSame(
            $this->getReference(LoadProductShippingOptions::PRODUCT_SHIPPING_OPTIONS_1)->getId(),
            $shippingOptions->getId()
        );
    }

    public function testShouldDeleteProductShippingOptionsWhenProductIsDeleted()
    {
        /** @var Product $product */
        $product = $this->getReference(LoadProductData::PRODUCT_1);

        // guard - the deleting product should have shipping options
        $this->assertInstanceOf(ProductShippingOptions::class, $this->findProductShippingOptionsByProduct($product));

        $this->delete(['entity' => 'products', 'id' => $product->getId()]);

        $this->assertNull($this->findProductShippingOptionsByProduct($product));
    }

    public function testGetSubresourceForShippingOptionsShouldReturnProductShippingOptions()
    {
        /** @var Product $product */
        $product = $this->getReference(LoadProductData::PRODUCT_1);

        $response = $this->getSubresource(
            ['entity' => 'products', 'id' => $product->getId(), 'association' => 'productShippingOptions']
        );

        $this->assertResponseContains(
            [
                'data' => [
                    [
                        'type'          => 'productshippingoptions',
                        'id'            => '<toString(@product_shipping_options.1->id)>',
                        'relationships' => [
                            'product' => [
                                'data' => [
                                    'type' => 'products',
                                    'id' => (string)$product->getId()
                                ]
                            ]
                        ]
                    ],
                    [
                        'type'          => 'productshippingoptions',
                        'id'            => '<toString(@product_shipping_options.2->id)>',
                        'relationships' => [
                            'product' => [
                                'data' => [
                                    'type' => 'products',
                                    'id' => (string)$product->getId()
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            $response
        );
    }

    public function testGetRelationshipForShippingOptionsShouldReturnProductShippingOptions()
    {
        /** @var Product $product */
        $product = $this->getReference(LoadProductData::PRODUCT_1);

        $response = $this->getRelationship(
            ['entity' => 'products', 'id' => $product->getId(), 'association' => 'productShippingOptions']
        );

        $this->assertResponseContains(
            [
                'data' => [
                    [
                        'type' => 'productshippingoptions',
                        'id'   => '<toString(@product_shipping_options.1->id)>'
                    ],
                    [
                        'type' => 'productshippingoptions',
                        'id'   => '<toString(@product_shipping_options.2->id)>'
                    ]
                ]
            ],
            $response
        );
    }

    public function testUpdateRelationshipForShippingOptionsShouldChangeProductShippingOptions()
    {
        /** @var Product $product */
        $product = $this->getReference(LoadProductData::PRODUCT_2);
        /** @var ProductShippingOptions $shippingOption */
        $shippingOption = $this->getReference(LoadProductShippingOptions::PRODUCT_SHIPPING_OPTIONS_1);

        $this->patchRelationship(
            ['entity' => 'products', 'id' => (string)$product->getId(), 'association' => 'productShippingOptions'],
            [
                'data' => [
                    [
                        'type' => 'productshippingoptions',
                        'id'   => '<toString(@product_shipping_options.1->id)>'
                    ]
                ]
            ]
        );

        $updatedShippingOptions = $this->findProductShippingOptionsByProduct($product);
        self::assertEquals($shippingOption->getId(), $updatedShippingOptions->getId());
    }
}
