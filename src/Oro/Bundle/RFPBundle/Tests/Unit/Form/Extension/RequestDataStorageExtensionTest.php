<?php

namespace Oro\Bundle\RFPBundle\Tests\Unit\Form\Extension;

use Oro\Bundle\EntityExtendBundle\Tests\Unit\Fixtures\TestEnumValue as InventoryStatus;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ProductBundle\Storage\ProductDataStorage;
use Oro\Bundle\ProductBundle\Tests\Unit\Form\Extension\AbstractProductDataStorageExtensionTestCase;
use Oro\Bundle\RFPBundle\Entity\Request as RFPRequest;
use Oro\Bundle\RFPBundle\Entity\RequestProduct;
use Oro\Bundle\RFPBundle\Entity\RequestProductItem;
use Oro\Bundle\RFPBundle\Form\Extension\RequestDataStorageExtension;
use Oro\Bundle\RFPBundle\Form\Type\Frontend\RequestType;
use Oro\Bundle\RFPBundle\Provider\ProductAvailabilityProvider;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class RequestDataStorageExtensionTest extends AbstractProductDataStorageExtensionTestCase
{
    /** @var ProductAvailabilityProvider|\PHPUnit\Framework\MockObject\MockObject */
    private $productAvailabilityProvider;

    /** @var TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $translator;

    /** @var Environment|\PHPUnit\Framework\MockObject\MockObject */
    private $twig;

    /** @var FlashBagInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $flashBag;

    /** @var RFPRequest */
    private $entity;

    /** @var RequestDataStorageExtension */
    protected $extension;

    protected function setUp(): void
    {
        $this->entity = new RFPRequest();

        parent::setUp();

        $this->productAvailabilityProvider = $this->createMock(ProductAvailabilityProvider::class);
        $this->translator = $this->createMock(TranslatorInterface::class);
        $this->twig = $this->createMock(Environment::class);
        $this->flashBag = $this->createMock(FlashBagInterface::class);

        $session = $this->createMock(Session::class);
        $session->expects($this->any())
            ->method('getFlashBag')
            ->willReturn($this->flashBag);

        $requestStack = $this->createMock(RequestStack::class);
        $requestStack->expects($this->any())
            ->method('getCurrentRequest')
            ->willReturn($this->request);
        $requestStack->expects($this->any())
            ->method('getSession')
            ->willReturn($session);

        $this->extension = new RequestDataStorageExtension(
            $requestStack,
            $this->storage,
            PropertyAccess::createPropertyAccessor(),
            $this->doctrine,
            $this->logger,
            $this->productAvailabilityProvider,
            $this->translator,
            $this->twig
        );

        $this->initEntityMetadata([]);
    }

    /**
     * {@inheritDoc}
     */
    protected function getTargetEntity(): RFPRequest
    {
        return $this->entity;
    }

    private function getInventoryStatus(string $id): InventoryStatus
    {
        return new InventoryStatus($id, $id);
    }

    public function testBuildForm(): void
    {
        $productId = 123;
        $sku = 'TEST';
        $qty = 3;
        $data = [
            ProductDataStorage::ENTITY_ITEMS_DATA_KEY => [
                [
                    ProductDataStorage::PRODUCT_ID_KEY => $productId,
                    ProductDataStorage::PRODUCT_SKU_KEY => $sku,
                    ProductDataStorage::PRODUCT_QUANTITY_KEY => $qty,
                ]
            ]
        ];

        $productUnit = $this->getProductUnit('item');
        $product = $this->getProduct($sku, $productUnit);

        $this->productAvailabilityProvider->expects($this->once())
            ->method('isProductApplicableForRFP')
            ->with($product)
            ->willReturn(true);
        $this->productAvailabilityProvider->expects($this->atLeastOnce())
            ->method('isProductAllowedForRFP')
            ->with($product)
            ->willReturn(true);

        $this->expectsGetStorageFromRequest();
        $this->expectsGetDataFromStorage($data);
        $this->expectsFindProduct($productId, $product);

        $this->extension->buildForm($this->getFormBuilder(), []);

        $this->assertCount(1, $this->entity->getRequestProducts());
        /** @var RequestProduct $requestProduct */
        $requestProduct = $this->entity->getRequestProducts()->first();

        $this->assertEquals($product, $requestProduct->getProduct());
        $this->assertEquals($product->getSku(), $requestProduct->getProductSku());

        $this->assertCount(1, $requestProduct->getRequestProductItems());
        /** @var RequestProductItem $requestProductItem */
        $requestProductItem = $requestProduct->getRequestProductItems()->first();

        $this->assertEquals($productUnit, $requestProductItem->getProductUnit());
        $this->assertEquals($productUnit->getCode(), $requestProductItem->getProductUnitCode());
        $this->assertEquals($qty, $requestProductItem->getQuantity());
    }

    public function testBuildFormNotAllowedForRFPProduct(): void
    {
        $productId = 123;
        $sku = 'TEST';
        $qty = 3;
        $data = [
            ProductDataStorage::ENTITY_ITEMS_DATA_KEY => [
                [
                    ProductDataStorage::PRODUCT_ID_KEY => $productId,
                    ProductDataStorage::PRODUCT_SKU_KEY => $sku,
                    ProductDataStorage::PRODUCT_QUANTITY_KEY => $qty,
                ]
            ]
        ];

        $productUnit = $this->getProductUnit('item');
        $product = $this->getProduct($sku, $productUnit);

        $this->productAvailabilityProvider->expects($this->once())
            ->method('isProductApplicableForRFP')
            ->with($product)
            ->willReturn(true);
        $this->productAvailabilityProvider->expects($this->atLeastOnce())
            ->method('isProductAllowedForRFP')
            ->with($product)
            ->willReturn(false);

        $this->expectsGetStorageFromRequest();
        $this->expectsGetDataFromStorage($data);
        $this->expectsFindProduct($productId, $product);

        $this->extension->buildForm($this->getFormBuilder(), []);

        $this->assertEmpty($this->entity->getRequestProducts());
    }

    public function testTheIsNoDisabledProductsInRequestProductsAfterExtensionBuild(): void
    {
        $productId = 123;
        $sku = 'TEST';
        $data = [
            ProductDataStorage::ENTITY_ITEMS_DATA_KEY => [
                [
                    ProductDataStorage::PRODUCT_ID_KEY => $productId,
                    ProductDataStorage::PRODUCT_SKU_KEY => $sku,
                    ProductDataStorage::PRODUCT_QUANTITY_KEY => 3,
                ]
            ]
        ];

        $productUnit = $this->getProductUnit('item');
        $product = $this->getProduct($sku, $productUnit);
        $product->setStatus(Product::STATUS_DISABLED);
        $product->setInventoryStatus($this->getInventoryStatus('in_stock'));

        $this->productAvailabilityProvider->expects($this->once())
            ->method('isProductApplicableForRFP')
            ->with($product)
            ->willReturn(true);

        $this->expectsGetStorageFromRequest();
        $this->expectsGetDataFromStorage($data);
        $this->expectsFindProduct($productId, $product);

        $this->extension->buildForm($this->getFormBuilder(), []);

        $this->assertEmpty($this->entity->getRequestProducts());
    }

    public function testBuildFormNotApplicableProduct(): void
    {
        $productId = 123;
        $sku = 'TEST';
        $qty = 3;
        $data = [
            ProductDataStorage::ENTITY_ITEMS_DATA_KEY => [
                [
                    ProductDataStorage::PRODUCT_ID_KEY => $productId,
                    ProductDataStorage::PRODUCT_SKU_KEY => $sku,
                    ProductDataStorage::PRODUCT_QUANTITY_KEY => $qty,
                ]
            ]
        ];

        $productUnit = $this->getProductUnit('item');
        $product = $this->getProduct($sku, $productUnit);
        $product->setStatus(Product::STATUS_ENABLED);
        $product->setInventoryStatus($this->getInventoryStatus('in_stock'));

        $this->productAvailabilityProvider->expects($this->once())
            ->method('isProductApplicableForRFP')
            ->with($product)
            ->willReturn(false);
        $this->productAvailabilityProvider->expects($this->never())
            ->method('isProductAllowedForRFP');

        $this->expectsGetStorageFromRequest();
        $this->expectsGetDataFromStorage($data);
        $this->expectsFindProduct($productId, $product);

        $this->extension->buildForm($this->getFormBuilder(), []);

        $this->assertEmpty($this->entity->getRequestProducts());
    }

    public function testBuildFormWithoutUnit(): void
    {
        $productId = 123;
        $sku = 'TEST';
        $qty = 3;
        $data = [
            ProductDataStorage::ENTITY_ITEMS_DATA_KEY => [
                [
                    ProductDataStorage::PRODUCT_ID_KEY => $productId,
                    ProductDataStorage::PRODUCT_SKU_KEY => $sku,
                    ProductDataStorage::PRODUCT_QUANTITY_KEY => $qty,
                ]
            ]
        ];

        $product = $this->getProduct($sku);

        $this->expectsGetStorageFromRequest();
        $this->expectsGetDataFromStorage($data);
        $this->expectsFindProduct($productId, $product);

        $this->extension->buildForm($this->getFormBuilder(), []);

        $this->assertEmpty($this->entity->getRequestProducts());
    }

    public function testGetExtendedTypes(): void
    {
        $this->assertEquals([RequestType::class], RequestDataStorageExtension::getExtendedTypes());
    }
}
