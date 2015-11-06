<?php

namespace OroB2B\Bundle\SaleBundle\Tests\Functional\Form\Type;

use OroB2B\Bundle\ProductBundle\Tests\Functional\Form\Type\ScopedProductSelectTypeTest;

/**
 * @dbIsolation
 */
class ProductSelectTypeTest extends ScopedProductSelectTypeTest
{
    public function setUp()
    {
        $this->setDataParameters(['scope' => 'quote']);
        $this->setConfigPath('oro_b2b_sale.backend_product_visibility');

        parent::setUp();
    }
}
