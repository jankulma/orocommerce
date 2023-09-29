<?php

namespace Oro\Bundle\TaxBundle\EventListener;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\FeatureToggleBundle\Checker\FeatureChecker;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\SecurityBundle\Form\FieldAclHelper;
use Oro\Bundle\TaxBundle\Entity\ProductTaxCode;
use Oro\Bundle\UIBundle\Event\BeforeListRenderEvent;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Adds tax code to the product view and edit pages.
 */
class ProductFormViewListener
{
    public function __construct(
        private RequestStack $requestStack,
        private DoctrineHelper $doctrineHelper,
        private FeatureChecker $featureChecker,
        private FieldAclHelper $fieldAclHelper
    ) {
    }

    public function onView(BeforeListRenderEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();
        if (null === $request) {
            return;
        }

        if (!$this->featureChecker->isResourceEnabled(ProductTaxCode::class, 'entities')) {
            return;
        }

        /** @var Product|null $product */
        $product = $this->doctrineHelper->getEntityReference(Product::class, (int)$request->get('id'));
        if (null === $product) {
            return;
        }

        if (!$this->fieldAclHelper->isFieldViewGranted($product, 'taxCode')) {
            return;
        }

        $template = $event->getEnvironment()->render(
            '@OroTax/Product/tax_code_view.html.twig',
            ['entity' => $product->getTaxCode()]
        );

        $event->getScrollData()->addSubBlockData('general', 1, $template);
    }

    public function onEdit(BeforeListRenderEvent $event): void
    {
        if (!$this->featureChecker->isResourceEnabled(ProductTaxCode::class, 'entities')) {
            return;
        }

        $product = $event->getEntity();
        if (!$this->fieldAclHelper->isFieldAvailable($product, 'taxCode')) {
            return;
        }

        $template = $event->getEnvironment()->render(
            '@OroTax/Product/tax_code_update.html.twig',
            ['form' => $event->getFormView()]
        );

        $event->getScrollData()->addSubBlockData('general', 1, $template);
    }
}
