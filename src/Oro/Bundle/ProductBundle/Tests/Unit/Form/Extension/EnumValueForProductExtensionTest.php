<?php

namespace Oro\Bundle\ProductBundle\Tests\Unit\Form\Extension;

use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\EntityConfigBundle\Config\ConfigInterface;
use Oro\Bundle\EntityConfigBundle\Config\ConfigManager;
use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;
use Oro\Bundle\EntityConfigBundle\Provider\ConfigProvider;
use Oro\Bundle\EntityExtendBundle\Form\Type\EnumValueType;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ProductBundle\Entity\ProductVariantLink;
use Oro\Bundle\ProductBundle\Entity\Repository\ProductRepository;
use Oro\Bundle\ProductBundle\Form\Extension\EnumValueForProductExtension;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Component\Form\FormConfigInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

class EnumValueForProductExtensionTest extends \PHPUnit_Framework_TestCase
{
    use EntityTrait;

    const ENUM_FIELD_NAME = 'enumField';

    /** @var DoctrineHelper|\PHPUnit_Framework_MockObject_MockObject */
    private $doctrineHelper;

    /** @var EnumValueForProductExtension */
    private $extension;

    /** @var FormInterface|\PHPUnit_Framework_MockObject_MockObject $form */
    private $form;

    /** @var FormConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $configInterface;

    /** @var ProductRepository|\PHPUnit_Framework_MockObject_MockObject */
    private $productRepository;

    /** @var ConfigManager|\PHPUnit_Framework_MockObject_MockObject */
    private $configManager;

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->doctrineHelper = $this->getMockBuilder(DoctrineHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->form = $this->createMock(FormInterface::class);
        $this->configInterface = $this->createMock(FormConfigInterface::class);
        $this->productRepository = $this->getMockBuilder(ProductRepository::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configManager = $this->getMockBuilder(ConfigManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->extension = new EnumValueForProductExtension($this->doctrineHelper, $this->configManager);
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        unset($this->doctrineHelper, $this->form, $this->configInterface, $this->productRepository, $this->extension);
    }

    public function testGetExtendedType()
    {
        $this->assertEquals(EnumValueType::class, $this->extension->getExtendedType());
    }

    public function testBuildViewWhenEmptyFormData()
    {
        $view = new FormView();

        $this->form->expects($this->once())
            ->method('getData')
            ->willReturn(null);

        $this->form->expects($this->never())
            ->method('getParent');

        $this->extension->buildView($view, $this->form, []);

        $this->assertArrayNotHasKey('tooltip', $view->vars);
        $this->assertArrayNotHasKey('tooltip_parameters', $view->vars);
    }

    public function testBuildViewWhenEmptyDataId()
    {
        $view = new FormView();

        $this->form->expects($this->once())
            ->method('getData')
            ->willReturn(['id' => null]);

        $this->form->expects($this->never())
            ->method('getParent');

        $this->extension->buildView($view, $this->form, []);

        $this->assertArrayNotHasKey('tooltip', $view->vars);
        $this->assertArrayNotHasKey('tooltip_parameters', $view->vars);
    }

    public function testBuildViewWhenConfigIdIsNull()
    {
        $view = new FormView();

        $this->prepareForm();

        $this->configInterface->expects($this->once())
            ->method('getOption')
            ->with('config_id')
            ->willReturn(null);

        $this->extension->buildView($view, $this->form, []);

        $this->assertArrayNotHasKey('tooltip', $view->vars);
        $this->assertArrayNotHasKey('tooltip_parameters', $view->vars);
    }

    public function testBuildViewWhenConfigIdIsNotSupportedClass()
    {
        $view = new FormView();

        $this->prepareForm();

        $this->configInterface->expects($this->once())
            ->method('getOption')
            ->with('config_id')
            ->willReturn(new \stdClass());

        $this->extension->buildView($view, $this->form, []);

        $this->assertArrayNotHasKey('tooltip', $view->vars);
        $this->assertArrayNotHasKey('tooltip_parameters', $view->vars);
    }

    public function testBuildViewForUnsupportedClass()
    {
        $view = new FormView();

        $this->prepareForm();

        $configId = new FieldConfigId('testScope', 'stdClass', 'field');

        $this->configInterface->expects($this->once())
            ->method('getOption')
            ->with('config_id')
            ->willReturn($configId);

        $this->extension->buildView($view, $this->form, []);

        $this->assertArrayNotHasKey('tooltip', $view->vars);
        $this->assertArrayNotHasKey('tooltip_parameters', $view->vars);
    }

    public function testBuildViewWhenEnumValueNotUsedByProductVariants()
    {
        $view = new FormView();

        $this->prepareForm();

        $configId = new FieldConfigId('testScope', Product::class, 'enumFieldName');

        $this->configInterface->expects($this->once())
            ->method('getOption')
            ->with('config_id')
            ->willReturn($configId);

        $this->doctrineHelper->expects($this->once())
            ->method('getEntityRepositoryForClass')
            ->with(Product::class)
            ->willReturn($this->productRepository);

        $this->productRepository->expects($this->once())
            ->method('findByAttributeValue')
            ->with(
                Product::TYPE_SIMPLE,
                'enumFieldName',
                1,
                false
            )
            ->willReturn([]);

        $attributeConfigProvider = $this->getAttributeProvider();
        $attributeConfig = $this->createMock(ConfigInterface::class);

        $attributeConfig->expects($this->once())
            ->method('is')
            ->with('is_attribute')
            ->willReturn(true);

        $attributeConfigProvider->expects($this->once())
            ->method('getConfig')
            ->willReturn($attributeConfig);

        $this->extension->buildView($view, $this->form, []);

        $this->assertArrayNotHasKey('tooltip', $view->vars);
        $this->assertArrayNotHasKey('tooltip_parameters', $view->vars);
    }

    /**
     * @dataProvider buildViewProvider
     * @param array $products
     * @param $expectedSkuList
     */
    public function testBuildView(array $products, $expectedSkuList)
    {
        $view = new FormView();

        $this->prepareForm();

        $configId = new FieldConfigId('testScope', Product::class, self::ENUM_FIELD_NAME);
        $this->configInterface->expects($this->once())
            ->method('getOption')
            ->with('config_id')
            ->willReturn($configId);

        $this->doctrineHelper->expects($this->once())
            ->method('getEntityRepositoryForClass')
            ->with(Product::class)
            ->willReturn($this->productRepository);

        $this->productRepository->expects($this->once())
            ->method('findByAttributeValue')
            ->with(
                Product::TYPE_SIMPLE,
                self::ENUM_FIELD_NAME,
                1,
                false
            )
            ->willReturn($products);

        $attributeConfigProvider = $this->getAttributeProvider();
        $attributeConfig = $this->createMock(ConfigInterface::class);

        $attributeConfig->expects($this->once())
            ->method('is')
            ->with('is_attribute')
            ->willReturn(true);

        $attributeConfigProvider->expects($this->once())
            ->method('getConfig')
            ->willReturn($attributeConfig);

        $this->extension->buildView($view, $this->form, []);

        $this->assertArrayHasKey('tooltip', $view->vars);
        $this->assertArrayHasKey('tooltip_parameters', $view->vars);
        $this->assertEquals(
            [
                '%skuList%' => $expectedSkuList
            ],
            $view->vars['tooltip_parameters']
        );
        $this->assertArrayHasKey('allow_delete', $view->vars);
        $this->assertFalse($view->vars['allow_delete']);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|ConfigProvider
     */
    private function getAttributeProvider()
    {
        $attributeConfigProvider = $this->getMockBuilder(ConfigProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configManager->expects($this->once())
            ->method('getProvider')
            ->with('attribute')
            ->willReturn($attributeConfigProvider);

        return $attributeConfigProvider;
    }

    /**
     * @return array
     */
    public function buildViewProvider()
    {
        $product = $this->prepareProduct('sku1', [self::ENUM_FIELD_NAME, 'test']);
        $product2 = $this->prepareProduct('sku2', [self::ENUM_FIELD_NAME]);
        $product3 = $this->prepareProduct('sku3', [self::ENUM_FIELD_NAME, 'field']);
        $product4 = $this->prepareProduct('sku4', [self::ENUM_FIELD_NAME, 'fieldName']);
        $product5 = $this->prepareProduct('sku5', [self::ENUM_FIELD_NAME, 'otherField']);
        $product6 = $this->prepareProduct('sku6', [self::ENUM_FIELD_NAME]);
        $product7 = $this->prepareProduct('sku7', [self::ENUM_FIELD_NAME]);
        $product8 = $this->prepareProduct('sku8', [self::ENUM_FIELD_NAME, 'field']);
        $product9 = $this->prepareProduct('sku9', [self::ENUM_FIELD_NAME]);
        $product10 = $this->prepareProduct('sku10', [self::ENUM_FIELD_NAME, 'fieldName']);
        $product11 = $this->prepareProduct('sku11', ['field']);
        $product12 = $this->prepareProduct('sku12', [self::ENUM_FIELD_NAME]);

        return [
            'with 3 config product sku' => [
                'products' => [$product, $product2, $product3],
                'expectedSkuList' => 'sku1, sku2, sku3'
            ],
            'with 3 config product sku, repository return 4' => [
                'products' => [$product, $product2, $product3, $product11],
                'expectedSkuList' => 'sku1, sku2, sku3'
            ],
            'with 11 config product sku' => [
                'products' => [
                    $product, $product2, $product3, $product4, $product5,
                    $product6, $product7, $product8, $product9, $product10, $product12
                ],
                'expectedSkuList' => 'sku1, sku2, sku3, sku4, sku5, sku6, sku7, sku8, sku9, sku10 ...',
            ],
            'with 1 config product sku' => [
                'products' => [$product11, $product],
                'expectedSkuList' => 'sku1',
            ]
        ];
    }

    /**
     * @param string $sku
     * @param array $variantFields
     * @return Product
     */
    private function prepareProduct($sku, array $variantFields)
    {
        $product = new Product();

        $parentProduct = $this->getEntity(
            Product::class,
            [
                'sku' => $sku,
                'variantFields' => $variantFields
            ]
        );

        $product->addParentVariantLink(new ProductVariantLink($parentProduct, $product));

        return $product;
    }

    private function prepareForm()
    {
        $this->form->expects($this->once())
            ->method('getData')
            ->willReturn(['id' => 1]);

        $parentForm = $this->createMock(FormInterface::class);

        $this->form->expects($this->once())
            ->method('getParent')
            ->willReturn($parentForm);

        $parentForm->expects($this->once())
            ->method('getConfig')
            ->willReturn($this->configInterface);
    }
}
