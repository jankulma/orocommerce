<?php

declare(strict_types=1);

namespace Oro\Bundle\PricingBundle\ProductKit\ProductPriceCriteria\Builder;

use Oro\Bundle\PricingBundle\Model\ProductPriceCriteriaBuilder\AbstractProductPriceCriteriaBuilder;
use Oro\Bundle\PricingBundle\ProductKit\ProductPriceCriteria\ProductKitItemPriceCriteria;
use Oro\Bundle\PricingBundle\ProductKit\ProductPriceCriteria\ProductKitPriceCriteria;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ProductBundle\Entity\ProductKitItem;
use Oro\Bundle\ProductBundle\Entity\ProductUnit;

/**
 * Creates {@see ProductKitPriceCriteria}.
 *
 * @method ProductKitPriceCriteria create()
 */
class ProductKitPriceCriteriaBuilder extends AbstractProductPriceCriteriaBuilder implements
    ProductKitPriceCriteriaBuilderInterface
{
    private array $kitItemsProducts = [];

    public function addKitItemProduct(
        ProductKitItem $productKitItem,
        Product $product,
        ProductUnit $productUnit,
        float $quantity
    ): self {
        $this->kitItemsProducts[$productKitItem->getId()] = [$productKitItem, $product, $productUnit, $quantity];

        return $this;
    }

    protected function doCreate(): ProductKitPriceCriteria
    {
        $productKitPriceCriteria = new ProductKitPriceCriteria(
            $this->product,
            $this->productUnit,
            $this->quantity,
            $this->getCurrencyWithFallback()
        );

        foreach ($this->kitItemsProducts as [$productKitItem, $product, $productUnit, $quantity]) {
            $productKitPriceCriteria->addKitItemProductPriceCriteria(
                new ProductKitItemPriceCriteria($productKitItem, $product, $productUnit, $quantity, $this->currency)
            );
        }


        return $productKitPriceCriteria;
    }

    public function isSupported(Product $product): bool
    {
        return $product->isKit();
    }

    public function reset(): void
    {
        parent::reset();

        $this->kitItemsProducts = [];
    }
}
