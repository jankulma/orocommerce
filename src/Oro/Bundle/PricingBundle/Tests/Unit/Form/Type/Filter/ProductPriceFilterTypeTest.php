<?php

namespace Oro\Bundle\PricingBundle\Tests\Unit\Form\Type\Filter;

use Doctrine\ORM\EntityManager;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectRepository;
use Oro\Bundle\FilterBundle\Form\Type\Filter\NumberRangeFilterType;
use Oro\Bundle\FilterBundle\Tests\Unit\Fixtures\CustomFormExtension;
use Oro\Bundle\FilterBundle\Tests\Unit\Form\Type\Filter\NumberRangeFilterTypeTest;
use Oro\Bundle\FormBundle\Form\Extension\NumberTypeExtension;
use Oro\Bundle\LocaleBundle\Formatter\Factory\IntlNumberFormatterFactory;
use Oro\Bundle\LocaleBundle\Formatter\NumberFormatter;
use Oro\Bundle\LocaleBundle\Model\LocaleSettings;
use Oro\Bundle\PricingBundle\Form\Type\Filter\ProductPriceFilterType;
use Oro\Bundle\ProductBundle\Entity\ProductUnit;
use Oro\Bundle\ProductBundle\Entity\Repository\ProductUnitRepository;
use Oro\Bundle\ProductBundle\Formatter\UnitLabelFormatterInterface;
use Oro\Component\Testing\Unit\PreloadedExtension;
use Symfony\Component\Form\Forms;

class ProductPriceFilterTypeTest extends NumberRangeFilterTypeTest
{
    /**
     * @var \PHPUnit\Framework\MockObject\MockObject|ManagerRegistry
     */
    protected $registry;

    /**
     * @var LocaleSettings|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $localeSettings;

    /**
     * @var NumberFormatter|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $numberFormatter;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        $this->localeSettings = $this->createMock(LocaleSettings::class);
        $locale = 'en';
        $this->localeSettings
            ->method('getLocale')
            ->willReturn($locale);

        \Locale::setDefault($locale);

        $this->numberFormatter = new NumberFormatter(
            $this->localeSettings,
            new IntlNumberFormatterFactory($this->localeSettings)
        );

        $translator = $this->createMockTranslator();

        $this->formExtensions = [
            new CustomFormExtension([new NumberRangeFilterType($translator)])
        ];

        /** @var \PHPUnit\Framework\MockObject\MockObject|UnitLabelFormatterInterface $formatter */
        $formatter = $this->createMock(UnitLabelFormatterInterface::class);
        $formatter->expects($this->any())
            ->method('format')
            ->with('item')
            ->willReturn('Item');

        parent::setUp();

        $this->type = new ProductPriceFilterType($translator, $this->getRegistry(), $formatter);
        $this->formExtensions[] = new PreloadedExtension([$this->type], []);

        $this->factory = Forms::createFormFactoryBuilder()
            ->addExtensions($this->getExtensions())
            ->addTypeExtensions($this->getTypeExtensions())
            ->getFormFactory();
    }

    protected function getTypeExtensions(): array
    {
        return [
            new NumberTypeExtension($this->numberFormatter),
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getTestFormType()
    {
        return $this->type;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|ManagerRegistry
     */
    public function getRegistry()
    {
        /** @var \PHPUnit\Framework\MockObject\MockObject|ProductUnit $productUnitMock */
        $productUnitMock = $this->createMock(ProductUnit::class);
        $productUnitMock->expects($this->any())
            ->method('getCode')
            ->willReturn('item');

        /** @var \PHPUnit\Framework\MockObject\MockObject|ObjectRepository $productUnitRepository */
        $productUnitRepository = $this->createMock(ProductUnitRepository::class);

        $productUnitRepository->expects($this->any())
            ->method('getAllUnitCodes')
            ->willReturn(['item']);

        /** @var \PHPUnit\Framework\MockObject\MockObject|EntityManager $entityManager */
        $entityManager = $this->createMock(EntityManager::class);
        $entityManager->expects($this->any())
            ->method('getRepository')
            ->with('OroProductBundle:ProductUnit')
            ->willReturn($productUnitRepository);

        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->registry->expects($this->any())
            ->method('getManagerForClass')
            ->with('OroProductBundle:ProductUnit')
            ->willReturn($entityManager);

        return $this->registry;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptionsDataProvider()
    {
        return [
            [
                'defaultOptions' => [
                    'data_type' => NumberRangeFilterType::DATA_DECIMAL,
                    'operator_choices' => [
                        'oro.filter.form.label_type_range_between' => NumberRangeFilterType::TYPE_BETWEEN,
                        'oro.filter.form.label_type_range_equals' => NumberRangeFilterType::TYPE_EQUAL,
                        'oro.filter.form.label_type_range_more_than' => NumberRangeFilterType::TYPE_GREATER_THAN,
                        'oro.filter.form.label_type_range_less_than' => NumberRangeFilterType::TYPE_LESS_THAN,
                        'oro.filter.form.label_type_range_more_equals' => NumberRangeFilterType::TYPE_GREATER_EQUAL,
                        'oro.filter.form.label_type_range_less_equals' => NumberRangeFilterType::TYPE_LESS_EQUAL,
                    ]
                ]
            ]
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function bindDataProvider()
    {
        $bindData = parent::bindDataProvider();

        /* ProductPriceFilterType doesn't have "not between" option */
        unset($bindData['not between range']);

        foreach ($bindData as $key => &$data) {
            $data['bindData']['unit'] = 'item';
            $data['formData']['unit'] = 'item';
            $data['viewData']['value']['unit'] = 'item';
        }

        return $bindData;
    }

    public function testGetParent()
    {
        $this->assertEquals(NumberRangeFilterType::class, $this->type->getParent());
    }
}
