<?php

namespace Oro\Bundle\TaxBundle\Tests\Unit\Form\Type;

use Oro\Bundle\FormBundle\Form\Type\OroPercentType;
use Oro\Bundle\TaxBundle\Entity\Tax;
use Oro\Bundle\TaxBundle\Form\Type\TaxType;
use Oro\Component\Testing\Unit\FormIntegrationTestCase;
use Oro\Component\Testing\Unit\PreloadedExtension;

class TaxTypeTest extends FormIntegrationTestCase
{
    const DATA_CLASS = 'Oro\Bundle\TaxBundle\Entity\Tax';

    /**
     * @var TaxType
     */
    protected $formType;

    /**
     * @return array
     */
    protected function getExtensions()
    {
        return [
            new PreloadedExtension(
                [
                    TaxType::class => $this->formType,
                    OroPercentType::class => new OroPercentType(),
                ],
                []
            )
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function setUp()
    {
        $this->formType = new TaxType();
        $this->formType->setDataClass(static::DATA_CLASS);
        parent::setUp();
    }

    /**
     * {@inheritdoc}
     */
    protected function tearDown()
    {
        unset($this->formType);

        parent::tearDown();
    }

    public function testBuildForm()
    {
        $form = $this->factory->create(TaxType::class);

        $this->assertTrue($form->has('code'));
        $this->assertTrue($form->has('description'));
        $this->assertTrue($form->has('rate'));

        $rate = $form->get('rate');
        $this->assertArrayHasKey('scale', $rate->getConfig()->getOptions());
        $this->assertEquals(TaxType::TAX_RATE_FIELD_PRECISION, $rate->getConfig()->getOptions()['scale']);
    }

    /**
     * @dataProvider submitDataProvider
     * @param bool   $isValid
     * @param mixed  $defaultData
     * @param mixed  $viewData
     * @param array  $submittedData
     * @param array  $expectedData
     */
    public function testSubmit(
        $isValid,
        $defaultData,
        $viewData,
        array $submittedData,
        $expectedData
    ) {
        $form = $this->factory->create(TaxType::class, $defaultData);

        $formConfig = $form->getConfig();
        $this->assertEquals(static::DATA_CLASS, $formConfig->getOption('data_class'));

        $this->assertEquals($defaultData, $form->getData());
        $this->assertEquals($viewData, $form->getViewData());

        $form->submit($submittedData);
        $this->assertEquals($isValid, $form->isValid());

        foreach ($expectedData as $field => $data) {
            $this->assertTrue($form->has($field));
            $fieldForm = $form->get($field);
            $this->assertEquals($data, $fieldForm->getData());
        }
    }

    /**
     * @return array
     */
    public function submitDataProvider()
    {
        $tax = new Tax();
        $tax->setCode('SomeCodeUnique1')
            ->setDescription('description')
            ->setRate(2);

        return [
            'new valid tax' => [
                'isValid' => true,
                'defaultData' => null,
                'viewData' => null,
                'submittedData' => [
                    'code' => 'SomeCodeUnique2',
                    'description' => 'description',
                    'rate' => '2.5'
                ],
                'expectedData' => [
                    'code' => 'SomeCodeUnique2',
                    'description' => 'description',
                    'rate' => '0.025'
                ]
            ],
            'update existing tax' => [
                'isValid' => true,
                'defaultData' => $tax,
                'viewData' => $tax,
                'submittedData' => [
                    'code' => 'SomeCode2',
                    'description' => 'description',
                    'rate' => '2',
                ],
                'expectedData' => [
                    'code' => 'SomeCode2',
                    'description' => 'description',
                    'rate' => '0.02',
                ]
            ]
        ];
    }
}
