<?php

namespace Oro\Bundle\PayPalBundle\EventListener;

use Oro\Bundle\PayPalBundle\Method\Config\PayflowGatewayConfigInterface;

use OroB2B\Bundle\PaymentBundle\Event\RequirePaymentRedirectEvent;

class PayflowRequirePaymentRedirectListener
{
    /**
     * @var PayflowGatewayConfigInterface
     */
    private $config;

    /**
     * @param PayflowGatewayConfigInterface $config
     */
    public function __construct(PayflowGatewayConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @param RequirePaymentRedirectEvent $event
     */
    public function onRequirePaymentRedirect(RequirePaymentRedirectEvent $event)
    {
        $event->setRedirect(!$this->config->isZeroAmountAuthorizationEnabled());
    }
}
