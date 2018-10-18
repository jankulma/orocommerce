<?php

namespace Oro\Bundle\RFPBundle\Tests\Unit\Form\Frontend\Type;

use Oro\Bundle\FormBundle\Form\Type\CollectionType;
use Oro\Bundle\RFPBundle\Form\Type\Frontend\RequestProductCollectionType;
use Oro\Bundle\RFPBundle\Form\Type\Frontend\RequestProductType;
use Symfony\Component\Form\Test\FormIntegrationTestCase;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RequestProductCollectionTypeTest extends FormIntegrationTestCase
{
    /**
     * @var RequestProductCollectionType
     */
    protected $formType;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();

        $this->formType = new RequestProductCollectionType();
    }

    public function testConfigureOptions()
    {
        /* @var $resolver \PHPUnit\Framework\MockObject\MockObject|OptionsResolver */
        $resolver = $this->createMock('Symfony\Component\OptionsResolver\OptionsResolver');
        $resolver->expects(static::once())
            ->method('setDefaults')
            ->with([
                'entry_type' => RequestProductType::class,
                'show_form_when_empty'  => true,
                'error_bubbling'        => false,
                'prototype_name'        => '__namerequestproduct__',
            ])
        ;

        $this->formType->configureOptions($resolver);
    }

    public function testGetParent()
    {
        static::assertEquals(CollectionType::class, $this->formType->getParent());
    }

    public function testGetName()
    {
        static::assertEquals(RequestProductCollectionType::NAME, $this->formType->getName());
    }
}
