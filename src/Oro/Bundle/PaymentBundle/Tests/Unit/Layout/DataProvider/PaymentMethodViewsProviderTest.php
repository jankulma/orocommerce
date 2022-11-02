<?php

namespace Oro\Bundle\PaymentBundle\Tests\Unit\Layout\DataProvider;

use Oro\Bundle\CacheBundle\Generator\UniversalCacheKeyGenerator;
use Oro\Bundle\PaymentBundle\Context\PaymentContextInterface;
use Oro\Bundle\PaymentBundle\Layout\DataProvider\PaymentMethodViewsProvider;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Oro\Bundle\PaymentBundle\Method\Provider\ApplicablePaymentMethodsProvider;
use Oro\Bundle\PaymentBundle\Method\View\CompositePaymentMethodViewProvider;
use Oro\Bundle\PaymentBundle\Method\View\PaymentMethodViewInterface;
use Oro\Bundle\PaymentBundle\Provider\PaymentTransactionProvider;
use Oro\Component\Testing\Unit\EntityTrait;
use PHPUnit\Framework\MockObject\Stub\ReturnCallback;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class PaymentMethodViewsProviderTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    const METHOD = 'Method';

    /**
     * @var CompositePaymentMethodViewProvider|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $paymentMethodViewProvider;

    /**
     * @var ApplicablePaymentMethodsProvider|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $paymentMethodProvider;

    /**
     * @var PaymentTransactionProvider|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $paymentTransactionProvider;

    /**
     * @var PaymentMethodViewsProvider
     */
    protected $provider;

    /**
     * @var CacheInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $cacheProvider;

    protected function setUp(): void
    {
        $this->paymentMethodViewProvider = $this
            ->getMockBuilder(CompositePaymentMethodViewProvider::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->paymentMethodProvider = $this->createMock(ApplicablePaymentMethodsProvider::class);

        $this->cacheProvider = $this->createMock(CacheInterface::class);

        $this->paymentTransactionProvider = $this->getMockBuilder(PaymentTransactionProvider::class)
            ->disableOriginalConstructor()->getMock();

        $this->provider = new PaymentMethodViewsProvider(
            $this->paymentMethodViewProvider,
            $this->paymentMethodProvider,
            $this->paymentTransactionProvider,
            $this->cacheProvider
        );
    }

    public function testGetViewsEmpty()
    {
        /** @var PaymentContextInterface $context */
        $context = $this->createMock(PaymentContextInterface::class);

        $this->paymentMethodProvider->expects(static::once())
            ->method('getApplicablePaymentMethods')
            ->with($context)
            ->willReturn([]);

        $this->paymentMethodViewProvider->expects(static::never())
            ->method('getPaymentMethodViews');

        $cacheKey = UniversalCacheKeyGenerator::normalizeCacheKey(
            PaymentMethodViewsProvider::class . \md5(\serialize($context))
        );

        $this->cacheProvider->expects($this->once())
            ->method('get')
            ->with($cacheKey)
            ->willReturnCallback(function ($cacheKey, $callback) {
                $item = $this->createMock(ItemInterface::class);
                return $callback($item);
            });

        $data = $this->provider->getViews($context);
        $this->assertEmpty($data);
    }

    public function testGetViews()
    {
        /** @var PaymentContextInterface $context */
        $context = $this->createMock(PaymentContextInterface::class);

        $methodType = 'payment_method';
        $paymentMethodViews = [$methodType => ['label' => 'label', 'block' => 'block', 'options' => []]];

        $cacheKey = UniversalCacheKeyGenerator::normalizeCacheKey(
            PaymentMethodViewsProvider::class . \md5(\serialize($context))
        );

        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $paymentMethod->expects(static::once())
            ->method('getIdentifier')
            ->willReturn($methodType);

        $this->paymentMethodProvider->expects(static::once())
            ->method('getApplicablePaymentMethods')
            ->with($context)
            ->willReturn([$paymentMethod]);

        $this->cacheProvider->expects($this->once())
            ->method('get')
            ->with($cacheKey)
            ->willReturnCallback(function ($cacheKey, $callback) {
                $item = $this->createMock(ItemInterface::class);
                return $callback($item);
            });

        $view = $this->createMock(PaymentMethodViewInterface::class);
        $view->expects($this->once())->method('getLabel')->willReturn('label');
        $view->expects($this->once())->method('getBlock')->willReturn('block');
        $view->expects($this->once())
            ->method('getOptions')
            ->with($context)
            ->willReturn([]);
        $view->expects($this->once())
            ->method('getPaymentMethodIdentifier')
            ->willReturn($methodType);

        $this->paymentMethodViewProvider->expects($this->once())
            ->method('getPaymentMethodViews')
            ->with([$methodType])
            ->willReturn([$view]);

        $data = $this->provider->getViews($context);
        $this->assertSame($paymentMethodViews, $data);
    }

    public function testGetViewsCached()
    {
        /** @var PaymentContextInterface $context */
        $context = $this->createMock(PaymentContextInterface::class);

        $methodType = 'payment_method';
        $paymentMethodViews = [$methodType => ['label' => 'label', 'block' => 'block', 'options' => []]];

        $cacheKey = UniversalCacheKeyGenerator::normalizeCacheKey(
            PaymentMethodViewsProvider::class . \md5(\serialize($context))
        );

        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $paymentMethod->expects(static::once())
            ->method('getIdentifier')
            ->willReturn($methodType);

        $this->paymentMethodProvider->expects(static::once())
            ->method('getApplicablePaymentMethods')
            ->with($context)
            ->willReturn([$paymentMethod]);

        $saveCallback = function ($cacheKey, $callback) {
            $item = $this->createMock(ItemInterface::class);
            return $callback($item);
        };
        $this->cacheProvider->expects($this->exactly(2))
            ->method('get')
            ->with($cacheKey)
            ->willReturnOnConsecutiveCalls(new ReturnCallback($saveCallback), $paymentMethodViews);

        $view = $this->createMock(PaymentMethodViewInterface::class);
        $view->expects($this->once())->method('getLabel')->willReturn('label');
        $view->expects($this->once())->method('getBlock')->willReturn('block');
        $view->expects($this->once())
            ->method('getOptions')
            ->with($context)
            ->willReturn([]);
        $view->expects($this->once())
            ->method('getPaymentMethodIdentifier')
            ->willReturn($methodType);

        $this->paymentMethodViewProvider->expects($this->once())
            ->method('getPaymentMethodViews')
            ->with([$methodType])
            ->willReturn([$view]);

        $data = $this->provider->getViews($context);
        $this->assertSame($paymentMethodViews, $data);

        $data = $this->provider->getViews($context);
        $this->assertSame($paymentMethodViews, $data);
    }

    public function testGetPaymentMethods()
    {
        $entity = new \stdClass();
        $this->paymentTransactionProvider->expects($this->once())->method('getPaymentMethods')->with($entity);
        $this->provider->getPaymentMethods($entity);
    }
}
