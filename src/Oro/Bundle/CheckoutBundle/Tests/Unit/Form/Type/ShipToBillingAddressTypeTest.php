<?php

namespace Oro\Bundle\CheckoutBundle\Tests\Unit\Form\Type;

use Oro\Bundle\CheckoutBundle\Form\Type\ShipToBillingAddressType;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Form;

class ShipToBillingAddressTypeTest extends FormIntegrationTestCase
{
    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        parent::setUp();
    }

    public function testSubmitParentFormWhenChecked()
    {
        $form = $this->createParentForm();

        $form->submit([
            ShipToBillingAddressType::SHIPPING_ADDRESS_FORM_FIELD => 'address',
            'test_type' => 1
        ]);

        $this->assertTrue($form->has(ShipToBillingAddressType::SHIPPING_ADDRESS_FORM_FIELD));
        $formConfig = $form->get(ShipToBillingAddressType::SHIPPING_ADDRESS_FORM_FIELD)->getConfig();
        $constraints = $formConfig->getOption('constraints');
        $this->assertInternalType('array', $constraints);
        $this->assertEquals([], $constraints);
    }

    public function testSubmitWhenAddressFormIsNotPresent()
    {
        $form = $this->factory->createBuilder()
            ->add('test_type', ShipToBillingAddressType::class)
            ->getForm();

        $form->submit(['test_type' => 1]);
        $this->assertFalse($form->has(ShipToBillingAddressType::SHIPPING_ADDRESS_FORM_FIELD));
    }

    public function testSubmitParentFormWhenNotChecked()
    {
        $form = $this->createParentForm();

        $form->submit([
            ShipToBillingAddressType::SHIPPING_ADDRESS_FORM_FIELD => 'address',
            'test_type' => 0
        ]);

        $this->assertTrue($form->has(ShipToBillingAddressType::SHIPPING_ADDRESS_FORM_FIELD));
    }

    /**
     * @return array
     */
    protected function getExtensions()
    {
        return [
            new PreloadedExtension(
                [ShipToBillingAddressType::class => new ShipToBillingAddressType()],
                []
            ),
            $this->getValidatorExtension(true),
        ];
    }

    /**
     * @return Form
     */
    protected function createParentForm()
    {
        $form = $this->factory->createBuilder()
            ->add('test_type', ShipToBillingAddressType::class)
            ->add(ShipToBillingAddressType::SHIPPING_ADDRESS_FORM_FIELD, TextType::class)
            ->getForm();

        return $form;
    }
}
