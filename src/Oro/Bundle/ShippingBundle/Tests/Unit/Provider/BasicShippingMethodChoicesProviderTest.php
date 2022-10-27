<?php

namespace Oro\Bundle\ShippingBundle\Tests\Unit\Provider;

use Oro\Bundle\ShippingBundle\Method\ShippingMethodProviderInterface;
use Oro\Bundle\ShippingBundle\Provider\BasicShippingMethodChoicesProvider;
use Oro\Bundle\ShippingBundle\Tests\Unit\Provider\Stub\ShippingMethodStub;
use Oro\Component\Testing\Unit\EntityTrait;
use Symfony\Contracts\Translation\TranslatorInterface;

class BasicShippingMethodChoicesProviderTest extends \PHPUnit\Framework\TestCase
{
    use EntityTrait;

    /** @var ShippingMethodProviderInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $shippingMethodProvider;

    /** @var TranslatorInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $translator;

    /** @var BasicShippingMethodChoicesProvider */
    private $choicesProvider;

    protected function setUp(): void
    {
        $this->shippingMethodProvider = $this->createMock(ShippingMethodProviderInterface::class);
        $this->translator = $this->createMock(TranslatorInterface::class);

        $this->choicesProvider = new BasicShippingMethodChoicesProvider(
            $this->shippingMethodProvider,
            $this->translator
        );
    }

    /**
     * @dataProvider methodsProvider
     */
    public function testGetMethods(array $methods, array $result, bool $translate = false)
    {
        $translation = [
            ['flat rate', [], null, null, 'flat rate translated'],
            ['ups', [], null, null, 'ups translated'],
        ];

        $this->shippingMethodProvider->expects($this->once())
            ->method('getShippingMethods')
            ->willReturn($methods);

        $this->translator->expects($this->any())
            ->method('trans')
            ->willReturnMap($translation);

        $this->assertEquals($result, $this->choicesProvider->getMethods($translate));
    }

    public function methodsProvider(): array
    {
        return
            [
                'some_methods' => [
                    'methods' => [
                        'flat_rate' => $this->getEntity(
                            ShippingMethodStub::class,
                            [
                                'identifier' => 'flat_rate',
                                'sortOrder' => 1,
                                'label' => 'flat rate',
                                'isEnabled' => true,
                                'types' => [],
                            ]
                        ),
                        'ups' => $this->getEntity(
                            ShippingMethodStub::class,
                            [
                                'identifier' => 'ups',
                                'sortOrder' => 1,
                                'label' => 'ups',
                                'isEnabled' => false,
                                'types' => [],
                            ]
                        ),
                    ],
                    'result' => ['flat rate' => 'flat_rate', 'ups' => 'ups'],
                    'translate' => false,
                ],
                'some_methods_with_translation' => [
                    'methods' => [
                        'flat_rate' => $this->getEntity(
                            ShippingMethodStub::class,
                            [
                                'identifier' => 'flat_rate',
                                'sortOrder' => 1,
                                'label' => 'flat rate',
                                'isEnabled' => true,
                                'types' => [],
                            ]
                        ),
                        'ups' => $this->getEntity(
                            ShippingMethodStub::class,
                            [
                                'identifier' => 'ups',
                                'sortOrder' => 1,
                                'label' => 'ups',
                                'isEnabled' => false,
                                'types' => [],
                            ]
                        ),
                    ],
                    'result' => ['flat rate translated' => 'flat_rate', 'ups translated' => 'ups'],
                    'translate' => true,
                ],
                'no_methods' => [
                    'methods' => [],
                    'result' => [],
                ],
            ];
    }
}
