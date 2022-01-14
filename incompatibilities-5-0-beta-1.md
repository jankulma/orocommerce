- [ApplicationBundle](#applicationbundle)
- [InventoryBundle](#inventorybundle)
- [OrderBundle](#orderbundle)
- [PricingBundle](#pricingbundle)
- [ProductBundle](#productbundle)
- [PromotionBundle](#promotionbundle)
- [RFPBundle](#rfpbundle)
- [RedirectBundle](#redirectbundle)
- [ShippingBundle](#shippingbundle)
- [ShoppingListBundle](#shoppinglistbundle)
- [TaxBundle](#taxbundle)
- [VisibilityBundle](#visibilitybundle)

ApplicationBundle
-----------------
* The following classes were removed:
   - `ModelRepository`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/ApplicationBundle/Repository/ModelRepository.php#L14 "Oro\Bundle\ApplicationBundle\Repository\ModelRepository")</sup>
   - `AbstractModel`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/ApplicationBundle/Model/AbstractModel.php#L8 "Oro\Bundle\ApplicationBundle\Model\AbstractModel")</sup>
   - `ModelFactory`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/ApplicationBundle/Factory/ModelFactory.php#L5 "Oro\Bundle\ApplicationBundle\Factory\ModelFactory")</sup>
   - `ModelEvent`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/ApplicationBundle/Event/ModelEvent.php#L8 "Oro\Bundle\ApplicationBundle\Event\ModelEvent")</sup>
   - `ModelIdentifierEvent`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/ApplicationBundle/Event/ModelIdentifierEvent.php#L7 "Oro\Bundle\ApplicationBundle\Event\ModelIdentifierEvent")</sup>
* The following interfaces were removed:
   - `ModelRepositoryInterface`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/ApplicationBundle/Repository/ModelRepositoryInterface.php#L11 "Oro\Bundle\ApplicationBundle\Repository\ModelRepositoryInterface")</sup>
   - `ModelInterface`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/ApplicationBundle/Model/ModelInterface.php#L9 "Oro\Bundle\ApplicationBundle\Model\ModelInterface")</sup>
   - `ModelFactoryInterface`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/ApplicationBundle/Factory/ModelFactoryInterface.php#L10 "Oro\Bundle\ApplicationBundle\Factory\ModelFactoryInterface")</sup>

InventoryBundle
---------------
* The `InventoryLevelsImportListener::onBatchStepCompleted(StepExecutionEvent $event)`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/InventoryBundle/EventListener/InventoryLevelsImportListener.php#L31 "Oro\Bundle\InventoryBundle\EventListener\InventoryLevelsImportListener")</sup> method was changed to `InventoryLevelsImportListener::onBatchStepCompleted(StepExecutionEvent $event)`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-beta.1/src/Oro/Bundle/InventoryBundle/EventListener/InventoryLevelsImportListener.php#L30 "Oro\Bundle\InventoryBundle\EventListener\InventoryLevelsImportListener")</sup>

OrderBundle
-----------
* The `AbstractFormEventListener::__construct(EngineInterface $engine, FormFactoryInterface $formFactory)`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/OrderBundle/EventListener/Order/AbstractFormEventListener.php#L27 "Oro\Bundle\OrderBundle\EventListener\Order\AbstractFormEventListener")</sup> method was changed to `AbstractFormEventListener::__construct(Environment $twig, FormFactoryInterface $formFactory)`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-beta.1/src/Oro/Bundle/OrderBundle/EventListener/Order/AbstractFormEventListener.php#L23 "Oro\Bundle\OrderBundle\EventListener\Order\AbstractFormEventListener")</sup>
* The `Order::setShipUntil($shipUntil)`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/OrderBundle/Entity/Order.php#L682 "Oro\Bundle\OrderBundle\Entity\Order")</sup> method was changed to `Order::setShipUntil(DateTime $shipUntil = null)`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-beta.1/src/Oro/Bundle/OrderBundle/Entity/Order.php#L680 "Oro\Bundle\OrderBundle\Entity\Order")</sup>
* The `UpdateOrderTotals::addOrderToUpdateTotals(ParameterBagInterface $sharedData, Order $order, FormInterface $form, $orderFieldName = null)`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/OrderBundle/Api/Processor/UpdateOrderTotals.php#L49 "Oro\Bundle\OrderBundle\Api\Processor\UpdateOrderTotals")</sup> method was changed to `UpdateOrderTotals::addOrderToUpdateTotals(SharedDataAwareContextInterface $context, Order $order, FormInterface $form, $orderFieldName = null)`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-beta.1/src/Oro/Bundle/OrderBundle/Api/Processor/UpdateOrderTotals.php#L37 "Oro\Bundle\OrderBundle\Api\Processor\UpdateOrderTotals")</sup>
* The `AbstractFormEventListener::$engine`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/OrderBundle/EventListener/Order/AbstractFormEventListener.php#L18 "Oro\Bundle\OrderBundle\EventListener\Order\AbstractFormEventListener::$engine")</sup> property was removed.

PricingBundle
-------------
* The following classes were removed:
   - `BatchCollectPriceListInitialStatuses`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/PricingBundle/Api/Processor/BatchCollectPriceListInitialStatuses.php#L14 "Oro\Bundle\PricingBundle\Api\Processor\BatchCollectPriceListInitialStatuses")</sup>
   - `BatchCollectPriceListsToUpdateCombinedPriceLists`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/PricingBundle/Api/Processor/BatchCollectPriceListsToUpdateCombinedPriceLists.php#L13 "Oro\Bundle\PricingBundle\Api\Processor\BatchCollectPriceListsToUpdateCombinedPriceLists")</sup>
   - `BatchCollectPriceListsToUpdateLexemes`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/PricingBundle/Api/Processor/BatchCollectPriceListsToUpdateLexemes.php#L13 "Oro\Bundle\PricingBundle\Api\Processor\BatchCollectPriceListsToUpdateLexemes")</sup>
   - `BatchCollectPriceRulePriceListsToUpdateLexemes`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/PricingBundle/Api/Processor/BatchCollectPriceRulePriceListsToUpdateLexemes.php#L13 "Oro\Bundle\PricingBundle\Api\Processor\BatchCollectPriceRulePriceListsToUpdateLexemes")</sup>
   - `BatchResetPriceRuleField`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/PricingBundle/Api/Processor/ProductPrice/BatchResetPriceRuleField.php#L15 "Oro\Bundle\PricingBundle\Api\Processor\ProductPrice\BatchResetPriceRuleField")</sup>
   - `BatchProductPriceFlushDataHandler`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/PricingBundle/Api/Handler/BatchProductPriceFlushDataHandler.php#L11 "Oro\Bundle\PricingBundle\Api\Handler\BatchProductPriceFlushDataHandler")</sup>
   - `BatchProductPriceFlushDataHandlerFactory`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/PricingBundle/Api/Handler/BatchProductPriceFlushDataHandlerFactory.php#L12 "Oro\Bundle\PricingBundle\Api\Handler\BatchProductPriceFlushDataHandlerFactory")</sup>
* The `BaseProductPriceRepository::findByPriceListIdAndProductIds(ShardManager $shardManager, $priceListId, $productIds, $getTierPrices = true, $currency = null, $productUnitCode = null, $orderBy = [ ... ])`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/PricingBundle/Entity/Repository/BaseProductPriceRepository.php#L195 "Oro\Bundle\PricingBundle\Entity\Repository\BaseProductPriceRepository")</sup> method was changed to `BaseProductPriceRepository::findByPriceListIdAndProductIds(ShardManager $shardManager, $priceListId, $productIds, $getTierPrices = true, $currency = null, $productUnitCode = null, $orderBy = [ ... ])`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-beta.1/src/Oro/Bundle/PricingBundle/Entity/Repository/BaseProductPriceRepository.php#L185 "Oro\Bundle\PricingBundle\Entity\Repository\BaseProductPriceRepository")</sup>
* The `CombinedProductPriceRepository::findByPriceListIdAndProductIds(ShardManager $shardManager, $priceListId, $productIds, $getTierPrices = true, $currency = null, $productUnitCode = null, $orderBy = [ ... ])`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/PricingBundle/Entity/Repository/CombinedProductPriceRepository.php#L351 "Oro\Bundle\PricingBundle\Entity\Repository\CombinedProductPriceRepository")</sup> method was changed to `CombinedProductPriceRepository::findByPriceListIdAndProductIds(ShardManager $shardManager, $priceListId, $productIds, $getTierPrices = true, $currency = null, $productUnitCode = null, $orderBy = [ ... ])`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-beta.1/src/Oro/Bundle/PricingBundle/Entity/Repository/CombinedProductPriceRepository.php#L332 "Oro\Bundle\PricingBundle\Entity\Repository\CombinedProductPriceRepository")</sup>
* The `CollectPriceListInitialStatuses::__construct`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/PricingBundle/Api/Processor/CollectPriceListInitialStatuses.php#L22 "Oro\Bundle\PricingBundle\Api\Processor\CollectPriceListInitialStatuses::__construct")</sup> method was removed.

ProductBundle
-------------
* The `ProductVariantIndexDataProviderDecorator::__construct(ProductIndexDataProviderInterface $originalProvider)`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/ProductBundle/Search/ProductVariantIndexDataProviderDecorator.php#L21 "Oro\Bundle\ProductBundle\Search\ProductVariantIndexDataProviderDecorator")</sup> method was changed to `ProductVariantIndexDataProviderDecorator::__construct(ProductIndexDataProviderInterface $originalProvider, Registry $registry)`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-beta.1/src/Oro/Bundle/ProductBundle/Search/ProductVariantIndexDataProviderDecorator.php#L17 "Oro\Bundle\ProductBundle\Search\ProductVariantIndexDataProviderDecorator")</sup>
* The `ConfigurableProductProvider::__construct(CustomFieldProvider $customFieldProvider, ProductVariantAvailabilityProvider $productVariantAvailabilityProvider, PropertyAccessor $propertyAccessor, ProductVariantFieldValueHandlerRegistry $fieldValueHandlerRegistry, TranslatorInterface $translator)`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/ProductBundle/Layout/DataProvider/ConfigurableProductProvider.php#L46 "Oro\Bundle\ProductBundle\Layout\DataProvider\ConfigurableProductProvider")</sup> method was changed to `ConfigurableProductProvider::__construct(CustomFieldProvider $customFieldProvider, PropertyAccessor $propertyAccessor, ProductVariantFieldValueHandlerRegistry $fieldValueHandlerRegistry, TranslatorInterface $translator)`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-beta.1/src/Oro/Bundle/ProductBundle/Layout/DataProvider/ConfigurableProductProvider.php#L35 "Oro\Bundle\ProductBundle\Layout\DataProvider\ConfigurableProductProvider")</sup>
* The `WebsiteSearchProductIndexDataProvider::getFields`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/ProductBundle/Search/WebsiteSearchProductIndexDataProvider.php#L93 "Oro\Bundle\ProductBundle\Search\WebsiteSearchProductIndexDataProvider::getFields")</sup> method was removed.
* The following properties in class `WebsiteSearchProductIndexDataProvider`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/ProductBundle/Search/WebsiteSearchProductIndexDataProvider.php#L22 "Oro\Bundle\ProductBundle\Search\WebsiteSearchProductIndexDataProvider")</sup> were removed:
   - `$attributeTypeRegistry`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/ProductBundle/Search/WebsiteSearchProductIndexDataProvider.php#L22 "Oro\Bundle\ProductBundle\Search\WebsiteSearchProductIndexDataProvider::$attributeTypeRegistry")</sup>
   - `$configurationProvider`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/ProductBundle/Search/WebsiteSearchProductIndexDataProvider.php#L25 "Oro\Bundle\ProductBundle\Search\WebsiteSearchProductIndexDataProvider::$configurationProvider")</sup>
   - `$indexFieldsProvider`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/ProductBundle/Search/WebsiteSearchProductIndexDataProvider.php#L28 "Oro\Bundle\ProductBundle\Search\WebsiteSearchProductIndexDataProvider::$indexFieldsProvider")</sup>
   - `$propertyAccessor`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/ProductBundle/Search/WebsiteSearchProductIndexDataProvider.php#L31 "Oro\Bundle\ProductBundle\Search\WebsiteSearchProductIndexDataProvider::$propertyAccessor")</sup>
* The `ConfigurableProductProvider::$productVariantAvailabilityProvider`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/ProductBundle/Layout/DataProvider/ConfigurableProductProvider.php#L22 "Oro\Bundle\ProductBundle\Layout\DataProvider\ConfigurableProductProvider::$productVariantAvailabilityProvider")</sup> property was removed.

PromotionBundle
---------------
* The `OrderAppliedPromotionEventListener::__construct(EngineInterface $engine, FormFactoryInterface $formFactory, AppliedPromotionManager $appliedPromotionManager)`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/PromotionBundle/EventListener/OrderAppliedPromotionEventListener.php#L26 "Oro\Bundle\PromotionBundle\EventListener\OrderAppliedPromotionEventListener")</sup> method was changed to `OrderAppliedPromotionEventListener::__construct(Environment $twig, FormFactoryInterface $formFactory, AppliedPromotionManager $appliedPromotionManager)`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-beta.1/src/Oro/Bundle/PromotionBundle/EventListener/OrderAppliedPromotionEventListener.php#L21 "Oro\Bundle\PromotionBundle\EventListener\OrderAppliedPromotionEventListener")</sup>
* The `Coupon::setValidFrom($validFrom)`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/PromotionBundle/Entity/Coupon.php#L387 "Oro\Bundle\PromotionBundle\Entity\Coupon")</sup> method was changed to `Coupon::setValidFrom(DateTime $validFrom = null)`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-beta.1/src/Oro/Bundle/PromotionBundle/Entity/Coupon.php#L381 "Oro\Bundle\PromotionBundle\Entity\Coupon")</sup>
The following methods in interface `DiscountLineItemInterface` were added (with link)
   - `getSubtotalAfterDiscounts`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-beta.1/src/Oro/Bundle/PromotionBundle/Discount/DiscountLineItemInterface.php#L71 "Oro\Bundle\PromotionBundle\Discount\DiscountLineItemInterface::getSubtotalAfterDiscounts")</sup>
   - `setSubtotalAfterDiscounts`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-beta.1/src/Oro/Bundle/PromotionBundle/Discount/DiscountLineItemInterface.php#L77 "Oro\Bundle\PromotionBundle\Discount\DiscountLineItemInterface::setSubtotalAfterDiscounts")</sup>

RFPBundle
---------
* The `Request::setShipUntil($shipUntil)`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/RFPBundle/Entity/Request.php#L576 "Oro\Bundle\RFPBundle\Entity\Request")</sup> method was changed to `Request::setShipUntil(DateTime $shipUntil = null)`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-beta.1/src/Oro/Bundle/RFPBundle/Entity/Request.php#L576 "Oro\Bundle\RFPBundle\Entity\Request")</sup>

RedirectBundle
--------------
* The `SlugUrlMatcher::__construct(RouterInterface $router, ManagerRegistry $registry, ScopeManager $scopeManager, MatchedUrlDecisionMaker $matchedUrlDecisionMaker, AclHelper $aclHelper, Mode $maintenanceMode)`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/RedirectBundle/Routing/SlugUrlMatcher.php#L90 "Oro\Bundle\RedirectBundle\Routing\SlugUrlMatcher")</sup> method was changed to `SlugUrlMatcher::__construct(RouterInterface $router, ManagerRegistry $registry, ScopeManager $scopeManager, MatchedUrlDecisionMaker $matchedUrlDecisionMaker, AclHelper $aclHelper, Mode $maintenanceMode)`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-beta.1/src/Oro/Bundle/RedirectBundle/Routing/SlugUrlMatcher.php#L82 "Oro\Bundle\RedirectBundle\Routing\SlugUrlMatcher")</sup>
* The `CanonicalUrlGenerator::__construct(ConfigManager $configManager, Cache $cache, RequestStack $requestStack, RoutingInformationProvider $routingInformationProvider, WebsiteUrlResolver $websiteSystemUrlResolver)`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/RedirectBundle/Generator/CanonicalUrlGenerator.php#L55 "Oro\Bundle\RedirectBundle\Generator\CanonicalUrlGenerator")</sup> method was changed to `CanonicalUrlGenerator::__construct(ConfigManager $configManager, Cache $cache, RequestStack $requestStack, RoutingInformationProvider $routingInformationProvider, WebsiteUrlResolver $websiteSystemUrlResolver, LocalizationProviderInterface $localizationProvider)`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-beta.1/src/Oro/Bundle/RedirectBundle/Generator/CanonicalUrlGenerator.php#L54 "Oro\Bundle\RedirectBundle\Generator\CanonicalUrlGenerator")</sup>
* The `RedirectExceptionListener::onKernelException(GetResponseForExceptionEvent $event)`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/RedirectBundle/EventListener/RedirectExceptionListener.php#L38 "Oro\Bundle\RedirectBundle\EventListener\RedirectExceptionListener")</sup> method was changed to `RedirectExceptionListener::onKernelException(ExceptionEvent $event)`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-beta.1/src/Oro/Bundle/RedirectBundle/EventListener/RedirectExceptionListener.php#L29 "Oro\Bundle\RedirectBundle\EventListener\RedirectExceptionListener")</sup>

ShippingBundle
--------------
* The `ShippingOptionsLineItemCollectionFactoryDecorator::__construct(ShippingLineItemCollectionFactoryInterface $decoratedFactory, DoctrineHelper $doctrineHelper, LineItemBuilderByLineItemFactoryInterface $builderByLineItemFactory)`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/ShippingBundle/Context/LineItem/Collection/ShippingOptions/Factory/ShippingOptionsLineItemCollectionFactoryDecorator.php#L43 "Oro\Bundle\ShippingBundle\Context\LineItem\Collection\ShippingOptions\Factory\ShippingOptionsLineItemCollectionFactoryDecorator")</sup> method was changed to `ShippingOptionsLineItemCollectionFactoryDecorator::__construct(ShippingLineItemCollectionFactoryInterface $decoratedFactory, ManagerRegistry $managerRegistry, LineItemBuilderByLineItemFactoryInterface $builderByLineItemFactory)`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-beta.1/src/Oro/Bundle/ShippingBundle/Context/LineItem/Collection/ShippingOptions/Factory/ShippingOptionsLineItemCollectionFactoryDecorator.php#L34 "Oro\Bundle\ShippingBundle\Context\LineItem\Collection\ShippingOptions\Factory\ShippingOptionsLineItemCollectionFactoryDecorator")</sup>

ShoppingListBundle
------------------
* The `ShoppingListEntityListener::__construct(DefaultUserProvider $defaultUserProvider, TokenAccessorInterface $tokenAccessor)`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/ShoppingListBundle/Entity/EntityListener/ShoppingListEntityListener.php#L24 "Oro\Bundle\ShoppingListBundle\Entity\EntityListener\ShoppingListEntityListener")</sup> method was changed to `ShoppingListEntityListener::__construct(DefaultUserProvider $defaultUserProvider, TokenAccessorInterface $tokenAccessor, ShoppingListLimitManager $shoppingListLimitManager)`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-beta.1/src/Oro/Bundle/ShoppingListBundle/Entity/EntityListener/ShoppingListEntityListener.php#L28 "Oro\Bundle\ShoppingListBundle\Entity\EntityListener\ShoppingListEntityListener")</sup>

TaxBundle
---------
* The `SkipOrderTaxRecalculationResolver::clearOrderRequiresTaxRecalculationCache`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/TaxBundle/OrderTax/Resolver/SkipOrderTaxRecalculationResolver.php#L178 "Oro\Bundle\TaxBundle\OrderTax\Resolver\SkipOrderTaxRecalculationResolver::clearOrderRequiresTaxRecalculationCache")</sup> method was removed.
* The `SkipOrderTaxRecalculationResolver::__construct(ManagerRegistry $doctrine, TaxManager $taxManager, FrontendHelper $frontendHelper)`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/TaxBundle/OrderTax/Resolver/SkipOrderTaxRecalculationResolver.php#L44 "Oro\Bundle\TaxBundle\OrderTax\Resolver\SkipOrderTaxRecalculationResolver")</sup> method was changed to `SkipOrderTaxRecalculationResolver::__construct(ManagerRegistry $doctrine, TaxManager $taxManager, FrontendHelper $frontendHelper, EventDispatcherInterface $eventDispatcher)`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-beta.1/src/Oro/Bundle/TaxBundle/OrderTax/Resolver/SkipOrderTaxRecalculationResolver.php#L32 "Oro\Bundle\TaxBundle\OrderTax\Resolver\SkipOrderTaxRecalculationResolver")</sup>

VisibilityBundle
----------------
* The `CategoryVisibleListener::onKernelController(FilterControllerEvent $event)`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-alpha.2/src/Oro/Bundle/VisibilityBundle/EventListener/CategoryVisibleListener.php#L66 "Oro\Bundle\VisibilityBundle\EventListener\CategoryVisibleListener")</sup> method was changed to `CategoryVisibleListener::onKernelController(ControllerEvent $event)`<sup>[[?]](https://github.com/oroinc/orocommerce/tree/5.0.0-beta.1/src/Oro/Bundle/VisibilityBundle/EventListener/CategoryVisibleListener.php#L59 "Oro\Bundle\VisibilityBundle\EventListener\CategoryVisibleListener")</sup>
