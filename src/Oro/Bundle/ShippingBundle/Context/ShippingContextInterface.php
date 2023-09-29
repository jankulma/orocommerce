<?php

namespace Oro\Bundle\ShippingBundle\Context;

use Doctrine\Common\Collections\Collection;
use Oro\Bundle\CurrencyBundle\Entity\Price;
use Oro\Bundle\CustomerBundle\Entity\CustomerOwnerAwareInterface;
use Oro\Bundle\LocaleBundle\Model\AddressInterface;
use Oro\Bundle\WebsiteBundle\Entity\Website;

/**
 * Describes Shipping Context.
 */
interface ShippingContextInterface extends CustomerOwnerAwareInterface
{
    /**
     * @return Collection<ShippingLineItem>
     */
    public function getLineItems();

    /**
     * @return AddressInterface|null
     */
    public function getBillingAddress();

    /**
     * @return AddressInterface
     */
    public function getShippingAddress();

    /**
     * @return AddressInterface
     */
    public function getShippingOrigin();

    /**
     * @return String|null
     */
    public function getPaymentMethod();

    /**
     * @return String|null
     */
    public function getCurrency();

    /**
     * @return Price|null
     */
    public function getSubtotal();

    /**
     * @return object
     */
    public function getSourceEntity();

    /**
     * @return mixed
     */
    public function getSourceEntityIdentifier();

    /**
     * @return Website|null
     */
    public function getWebsite();
}
