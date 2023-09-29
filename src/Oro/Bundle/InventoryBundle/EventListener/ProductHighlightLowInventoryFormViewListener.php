<?php

namespace Oro\Bundle\InventoryBundle\EventListener;

use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\UIBundle\Event\BeforeListRenderEvent;
use Oro\Bundle\UIBundle\Fallback\AbstractFallbackFieldsFormView;

/**
 * Adds low inventory information to the product view and edit pages.
 */
class ProductHighlightLowInventoryFormViewListener extends AbstractFallbackFieldsFormView
{
    public function onProductView(BeforeListRenderEvent $event): void
    {
        $product = $this->getEntityFromRequest(Product::class);
        if (!$product) {
            return;
        }

        if (!$this->fieldAclHelper->isFieldViewGranted($product, 'highlightLowInventory')) {
            return;
        }

        $this->addBlockToEntityView(
            $event,
            '@OroInventory/Product/highlightLowInventory.html.twig',
            $product,
            'oro.product.sections.inventory'
        );
    }

    public function onProductEdit(BeforeListRenderEvent $event): void
    {
        $product = $event->getEntity();
        if (!$this->fieldAclHelper->isFieldAvailable($product, 'highlightLowInventory')) {
            return;
        }

        $this->addBlockToEntityEdit(
            $event,
            '@OroInventory/Product/highlightLowInventoryFormWidget.html.twig',
            'oro.product.sections.inventory'
        );
    }
}
