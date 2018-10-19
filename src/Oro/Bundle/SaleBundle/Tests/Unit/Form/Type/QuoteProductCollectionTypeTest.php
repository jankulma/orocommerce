<?php

namespace Oro\Bundle\SaleBundle\Tests\Unit\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\CollectionType;
use Oro\Bundle\SaleBundle\Form\Type\QuoteProductCollectionType;
use Oro\Bundle\SaleBundle\Form\Type\QuoteProductType;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QuoteProductCollectionTypeTest extends FormIntegrationTestCase
{
    /**
     * @var QuoteProductCollectionType
     */
    protected $formType;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->formType = new QuoteProductCollectionType();
    }

    public function testConfigureOptions()
    {
        /* @var $resolver \PHPUnit\Framework\MockObject\MockObject|OptionsResolver */
        $resolver = $this->createMock('Symfony\Component\OptionsResolver\OptionsResolver');
        $resolver->expects($this->once())
            ->method('setDefaults')
            ->with([
                    'entry_type'  => QuoteProductType::class,
                    'show_form_when_empty'  => true,
                    'error_bubbling'        => false,
                    'prototype_name'        => '__namequoteproduct__',
            ])
        ;

        $this->formType->configureOptions($resolver);
    }

    public function testGetParent()
    {
        $this->assertEquals(CollectionType::class, $this->formType->getParent());
    }
}
