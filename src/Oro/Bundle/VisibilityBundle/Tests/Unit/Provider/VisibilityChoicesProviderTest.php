<?php

namespace Oro\Bundle\VisibilityBundle\Tests\Unit\Provider;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Oro\Bundle\CatalogBundle\Entity\Category;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\VisibilityBundle\Provider\VisibilityChoicesProvider;
use Symfony\Bundle\FrameworkBundle\Tests\Templating\Helper\Fixtures\StubTranslator;

class VisibilityChoicesProviderTest extends \PHPUnit\Framework\TestCase
{
    const VISIBILITY_CLASS = 'Oro\Bundle\VisibilityBundle\Entity\Visibility\CategoryVisibility';

    /**
     * @var VisibilityChoicesProvider
     */
    protected $formatter;

    /**
     * @var Registry|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $registry;

    public function setUp()
    {
        $translator = new StubTranslator();
        $this->registry = $this->getMockBuilder('Doctrine\Bundle\DoctrineBundle\Registry')
            ->disableOriginalConstructor()
            ->getMock();
        $this->formatter = new VisibilityChoicesProvider($translator, $this->registry);
    }

    public function testGetFormattedChoices()
    {
        $actual = $this->formatter->getFormattedChoices(self::VISIBILITY_CLASS, $this->createCategory());
        $expected = [
            '[trans]oro.visibility.categoryvisibility.choice.parent_category[/trans]' => 'parent_category',
            '[trans]oro.visibility.categoryvisibility.choice.config[/trans]' => 'config',
            '[trans]oro.visibility.categoryvisibility.choice.hidden[/trans]' => 'hidden',
            '[trans]oro.visibility.categoryvisibility.choice.visible[/trans]' => 'visible',
        ];
        $this->assertEquals($expected, $actual);
    }

    /**
     * @dataProvider getChoicesForCategoryDataProvider
     * @param array $expected
     */
    public function testGetChoicesForCategory(array $expected)
    {
        $actual = $this->formatter->getChoices(self::VISIBILITY_CLASS, $this->createCategory());
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function getChoicesForCategoryDataProvider()
    {
        return [
           'target category' => [
                'expected' => [
                    'parent_category',
                    'config',
                    'hidden',
                    'visible',
                ]
            ]
        ];
    }

    /**
     * @dataProvider getChoicesForProductDataProvider
     * @param Category|null $productCategory
     * @param array $expected
     */
    public function testGetChoicesForProduct($productCategory, array $expected)
    {
        $repository = $this->getMockBuilder('Oro\Bundle\CatalogBundle\Entity\Repository\CategoryRepository')
            ->disableOriginalConstructor()
            ->getMock();
        $repository->expects($this->any())
            ->method('findOneByProduct')
            ->willReturn($productCategory);

        $em = $this->createMock('Doctrine\Common\Persistence\ObjectManager');
        $em->expects($this->any())
            ->method('getRepository')
            ->willReturn($repository);

        $this->registry->expects($this->any())
            ->method('getManagerForClass')
            ->willReturn($em);

        $actual = $this->formatter->getChoices(
            'Oro\Bundle\VisibilityBundle\Entity\Visibility\ProductVisibility',
            new Product()
        );
        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function getChoicesForProductDataProvider()
    {
        return [
            'target product with category' => [
                'productCategory' => new Category(),
                'expected' => [
                    'category',
                    'config',
                    'hidden',
                    'visible',
                ]
            ],
            'target product without category' => [
                'productCategory' => null,
                'expected' => [
                    'config',
                    'hidden',
                    'visible',
                ]
            ]
        ];
    }

    public function testFormatChoices()
    {
        $actual = $this->formatter->formatChoices('test.%s', ['test_1', 'test_2']);
        $expected = [
            '[trans]test.test_1[/trans]' => 'test_1',
            '[trans]test.test_2[/trans]' => 'test_2',
        ];
        $this->assertEquals($expected, $actual);
    }

    public function testFormat()
    {
        $actual = $this->formatter->format('test.%s', 'test_1');
        $this->assertEquals('[trans]test.test_1[/trans]', $actual);
    }

    /**
     * @return $this
     */
    protected function createCategory()
    {
        return (new Category())->setParentCategory(new Category());
    }
}
