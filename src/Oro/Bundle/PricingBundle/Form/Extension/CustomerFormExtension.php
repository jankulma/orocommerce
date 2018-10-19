<?php

namespace Oro\Bundle\PricingBundle\Form\Extension;

use Oro\Bundle\CustomerBundle\Form\Type\CustomerType;
use Oro\Bundle\PricingBundle\Entity\PriceListCustomerFallback;
use Oro\Bundle\PricingBundle\EventListener\CustomerListener;
use Oro\Bundle\PricingBundle\Form\Type\PriceListsSettingsType;
use Oro\Bundle\WebsiteBundle\Form\Type\WebsiteScopedDataType;
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvents;

class CustomerFormExtension extends AbstractTypeExtension
{
    /**
     * @var CustomerListener
     */
    protected $listener;

    /**
     * @var string
     */
    protected $relationClass = 'Oro\Bundle\PricingBundle\Entity\PriceListToCustomer';

    /**
     * @param CustomerListener $listener
     */
    public function __construct(CustomerListener $listener)
    {
        $this->listener = $listener;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return CustomerType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add(
            CustomerListener::PRICE_LISTS_COLLECTION_FORM_FIELD_NAME,
            WebsiteScopedDataType::class,
            [
                'type' => PriceListsSettingsType::class,
                'options' => [
                    PriceListsSettingsType::PRICE_LIST_RELATION_CLASS => $this->relationClass,
                    PriceListsSettingsType::FALLBACK_CHOICES => $this->getFallbackChoices(),
                ],
                'label' => false,
                'required' => false,
                'mapped' => false,
                'data' => [],
            ]
        );

        $builder->addEventListener(FormEvents::POST_SET_DATA, [$this->listener, 'onPostSetData']);
    }


    /**
     * @return array
     */
    protected function getFallbackChoices()
    {
        return [
            'oro.pricing.fallback.customer_group.label' =>
                PriceListCustomerFallback::ACCOUNT_GROUP,
            'oro.pricing.fallback.current_customer_only.label' =>
                PriceListCustomerFallback::CURRENT_ACCOUNT_ONLY,
        ];
    }

    /**
     * @param string $relationClass
     */
    public function setRelationClass($relationClass)
    {
        $this->relationClass = $relationClass;
    }
}
