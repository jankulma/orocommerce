<?php

namespace Oro\Bundle\ProductBundle\Tests\Unit\Form\Type\Stub;

use Oro\Bundle\ProductBundle\Form\Type\ProductSelectType;
use Oro\Component\Testing\Unit\Form\Type\Stub\EntityType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductSelectEntityTypeStub extends EntityType
{
    /**
     * {@inheritdoc}
     */
    public function __construct(array $choices)
    {
        parent::__construct($choices, ProductSelectType::NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choice_list'     => $this->choiceList,
            'query_builder'   => null,
            'create_enabled'  => false,
            'class'           => 'Oro\Bundle\ProductBundle\Entity\Product',
            'data_parameters' => [],
            'property'        => 'sku',
            'configs'         => [
                'placeholder' => null,
            ],
        ]);
    }
}
