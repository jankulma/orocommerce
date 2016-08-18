<?php

namespace OroB2B\Bundle\PaymentBundle\Layout\DataProvider;

use Oro\Component\Layout\AbstractServerRenderDataProvider;
use OroB2B\Bundle\PaymentBundle\Entity\PaymentMethodAwareInterface;

class PaymentMethodWidgetProvider extends AbstractServerRenderDataProvider
{
    const NAME = 'orob2b_payment_method_widget_provider';

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return self::NAME;
    }

    /**
     * @param object $entity
     * @param string $prefix
     * @return string
     */
    public function getPaymentMethodWidgetName($entity, $prefix)
    {
        if ($entity instanceof PaymentMethodAwareInterface) {
            return '_' . $entity->getPaymentMethod() . '_' . $prefix . '_widget';
        }

        return '';
    }
}
