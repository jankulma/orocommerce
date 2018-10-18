<?php

namespace Oro\Bundle\PricingBundle\Tests\Unit\EventListener;

use Doctrine\ORM\EntityRepository;
use Oro\Bundle\PricingBundle\Entity\PriceAttributePriceList;
use Oro\Bundle\PricingBundle\EventListener\FormViewListener;
use Oro\Bundle\PricingBundle\Provider\PriceAttributePricesProvider;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\UIBundle\Event\BeforeListRenderEvent;
use Oro\Bundle\UIBundle\View\ScrollData;
use Oro\Component\Testing\Unit\FormViewListenerTestCase;
use Symfony\Component\Form\FormView;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class FormViewListenerTest extends FormViewListenerTestCase
{
    /** @var FormViewListener */
    private $listener;

    /** @var AuthorizationCheckerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $authorizationChecker;

    /** @var \PHPUnit\Framework\MockObject\MockObject|\Twig_Environment */
    private $environment;

    /** @var PriceAttributePricesProvider|\PHPUnit\Framework\MockObject\MockObject */
    protected $priceAttributePricesProvider;

    protected function setUp()
    {
        $this->priceAttributePricesProvider = $this->createMock(PriceAttributePricesProvider::class);

        parent::setUp();

        $this->authorizationChecker = $this->createMock(AuthorizationCheckerInterface::class);

        $this->listener = new FormViewListener(
            $this->translator,
            $this->doctrineHelper,
            $this->priceAttributePricesProvider,
            $this->authorizationChecker
        );
    }

    public function testOnProductView()
    {
        $product = new Product();
        $templateHtmlProductAttributePrice = 'template_html_product_attribute_price';
        $templateHtmlProductPrice = 'template_html_product_price';

        $this->assertPriceAttributeViewRendered($product, $templateHtmlProductAttributePrice);

        $environment = $this->getEnvironment();
        $environment->expects($this->at(1))
            ->method('render')
            ->with(
                'OroPricingBundle:Product:prices_view.html.twig',
                [
                    'entity' => $product,
                ]
            )
            ->willReturn($templateHtmlProductPrice);

        $this->authorizationChecker
            ->expects($this->once())
            ->method('isGranted')
            ->with('VIEW', 'entity:Oro\Bundle\PricingBundle\Entity\ProductPrice')
            ->willReturn(true);

        $event = $this->createEvent($environment, $product);
        $this->listener->onProductView($event);
        $scrollData = $event->getScrollData()->getData();

        $this->assertEquals(
            'oro.pricing.pricelist.entity_plural_label.trans',
            $scrollData[ScrollData::DATA_BLOCKS]['prices'][ScrollData::TITLE]
        );

        $this->assertEquals(
            ['prices' => $templateHtmlProductPrice],
            $scrollData[ScrollData::DATA_BLOCKS]['prices'][ScrollData::SUB_BLOCKS][0][ScrollData::DATA]
        );

        $this->assertEquals(
            'oro.pricing.priceattributepricelist.entity_plural_label.trans',
            $scrollData[ScrollData::DATA_BLOCKS]['price_attributes'][ScrollData::TITLE]
        );

        $this->assertEquals(
            ['productPriceAttributesPrices' => $templateHtmlProductAttributePrice],
            $scrollData[ScrollData::DATA_BLOCKS]['price_attributes'][ScrollData::SUB_BLOCKS][0][ScrollData::DATA]
        );
    }

    public function testOnProductViewForbiddenToViewPrice()
    {
        $product = new Product();
        $templateHtmlProductAttributePrice = 'template_html_product_attribute_price';

        $this->assertPriceAttributeViewRendered($product, $templateHtmlProductAttributePrice);

        $this->authorizationChecker
            ->expects($this->once())
            ->method('isGranted')
            ->with('VIEW', 'entity:Oro\Bundle\PricingBundle\Entity\ProductPrice')
            ->willReturn(false);

        $event = $this->createEvent($this->getEnvironment(), $product);
        $this->listener->onProductView($event);
        $scrollData = $event->getScrollData()->getData();

        $this->assertTrue(empty($scrollData[ScrollData::DATA_BLOCKS]['prices']));

        $this->assertEquals(
            'oro.pricing.priceattributepricelist.entity_plural_label.trans',
            $scrollData[ScrollData::DATA_BLOCKS]['price_attributes'][ScrollData::TITLE]
        );

        $this->assertEquals(
            ['productPriceAttributesPrices' => $templateHtmlProductAttributePrice],
            $scrollData[ScrollData::DATA_BLOCKS]['price_attributes'][ScrollData::SUB_BLOCKS][0][ScrollData::DATA]
        );
    }

    public function testOnProductEdit()
    {
        $formView = new FormView();
        $templateHtml = 'template_html';

        /** @var \PHPUnit\Framework\MockObject\MockObject|\Twig_Environment $environment */
        $environment = $this->createMock(\Twig_Environment::class);
        $environment->expects($this->once())
            ->method('render')
            ->with('OroPricingBundle:Product:prices_update.html.twig', ['form' => $formView])
            ->willReturn($templateHtml);

        $entity = new Product();
        $event = $this->createEvent($environment, $entity, $formView);

        $this->listener->onProductEdit($event);
        $scrollData = $event->getScrollData()->getData();

        $expectedTitle = 'oro.pricing.productprice.entity_plural_label.trans';

        $this->assertEquals(
            $expectedTitle,
            $scrollData[ScrollData::DATA_BLOCKS]['prices'][ScrollData::TITLE]
        );

        $this->assertEquals(
            ['productPriceAttributesPrices' => $templateHtml],
            $scrollData[ScrollData::DATA_BLOCKS]['prices'][ScrollData::SUB_BLOCKS][0][ScrollData::DATA]
        );
    }

    /**
     * @param \Twig_Environment $environment
     * @param object $entity
     * @param FormView $formView
     * @return BeforeListRenderEvent
     */
    protected function createEvent(\Twig_Environment $environment, $entity, FormView $formView = null)
    {
        $defaultData = [
            ScrollData::DATA_BLOCKS => [
                [
                    ScrollData::SUB_BLOCKS => [
                        [
                            ScrollData::DATA => [],
                        ],
                    ],
                ],
            ],
        ];

        return new BeforeListRenderEvent($environment, new ScrollData($defaultData), $entity, $formView);
    }

    private function assertPriceAttributeViewRendered($product, $templateHtmlProductAttributePrice)
    {
        $priceList = new PriceAttributePriceList();

        /** @var \PHPUnit\Framework\MockObject\MockObject|EntityRepository $priceAttributePriceListRepository */
        $priceAttributePriceListRepository = $this->createMock(EntityRepository::class);

        $priceAttributePriceListRepository->expects($this->once())
            ->method('findAll')->willReturn([$priceList]);

        $this->doctrineHelper->expects($this->any())
            ->method('getEntityRepository')
            ->willReturnMap([
                ['OroPricingBundle:PriceAttributePriceList', $priceAttributePriceListRepository],
            ]);

        $this->priceAttributePricesProvider->expects($this->once())->method('getPricesWithUnitAndCurrencies')
            ->with($priceList, $product)
            ->willReturn(['Test' => ['item' => ['USD' => 100]]]);

        /** @var \PHPUnit\Framework\MockObject\MockObject|\Twig_Environment $environment */
        $environment = $this->getEnvironment();
        $environment->expects($this->at(0))
            ->method('render')
            ->with(
                'OroPricingBundle:Product:price_attribute_prices_view.html.twig',
                [
                    'product' => $product,
                    'priceList' => $priceList,
                    'priceAttributePrices' => ['Test' => ['item' => ['USD' => 100]]],
                ]
            )
            ->willReturn($templateHtmlProductAttributePrice);
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|\Twig_Environment
     */
    private function getEnvironment()
    {
        if (null === $this->environment) {
            $this->environment = $this->createMock(\Twig_Environment::class);
        }

        return $this->environment;
    }
}
