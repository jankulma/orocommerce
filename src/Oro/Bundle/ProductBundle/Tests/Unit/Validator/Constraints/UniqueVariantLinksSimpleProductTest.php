<?php

namespace Oro\Bundle\ProductBundle\Test\Validator\Constraints;

use Oro\Bundle\ProductBundle\Validator\Constraints\UniqueVariantLinksSimpleProduct;
use Oro\Bundle\ProductBundle\Validator\Constraints\UniqueVariantLinksSimpleProductValidator;
use Symfony\Component\Validator\Constraint;

class UniqueVariantLinksSimpleProductTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var UniqueVariantLinksSimpleProduct
     */
    protected $constraint;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->constraint = new UniqueVariantLinksSimpleProduct();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        unset($this->constraint);
    }

    public function testValidatedBy()
    {
        $this->assertEquals(UniqueVariantLinksSimpleProductValidator::ALIAS, $this->constraint->validatedBy());
    }

    public function testGetTargets()
    {
        $this->assertEquals(Constraint::CLASS_CONSTRAINT, $this->constraint->getTargets());
    }
}
