<?php

namespace Oro\Bundle\ProductBundle\Async;

/**
 * Product related message queue topics.
 */
class Topics
{
    public const REINDEX_PRODUCTS_BY_ATTRIBUTES = 'oro_product.reindex_products_by_attributes';
    public const PRODUCT_IMAGE_RESIZE = 'oro_product.image_resize';
}
