<?php

namespace Oro\Bundle\PaymentBundle\Tests\Unit\Action;

use Oro\Bundle\PaymentBundle\Action\PaymentTransactionCancelAction;
use Oro\Bundle\PaymentBundle\Entity\PaymentTransaction;
use Oro\Bundle\PaymentBundle\Method\PaymentMethodInterface;
use Symfony\Component\OptionsResolver\Exception\UndefinedOptionsException;
use Symfony\Component\PropertyAccess\PropertyPath;

class PaymentTransactionCancelActionTest extends AbstractActionTest
{
    /**
     * @dataProvider executeDataProvider
     */
    public function testExecute(array $data, array $expected)
    {
        /** @var PaymentTransaction $authorizationPaymentTransaction */
        $authorizationPaymentTransaction = $data['options']['paymentTransaction'];
        $cancelPaymentTransaction = $data['cancelPaymentTransaction'];
        $options = $data['options'];
        $context = [];

        $this->contextAccessor->expects(self::any())
            ->method('getValue')
            ->willReturnArgument(1);

        $this->paymentTransactionProvider->expects(self::once())
            ->method('createPaymentTransactionByParentTransaction')
            ->with(PaymentMethodInterface::CANCEL, $authorizationPaymentTransaction)
            ->willReturn($cancelPaymentTransaction);

        $responseValue = $this->returnValue($data['response']);

        if ($data['response'] instanceof \Exception) {
            $responseValue = $this->throwException($data['response']);
        }

        $paymentMethod = $this->createMock(PaymentMethodInterface::class);
        $paymentMethod->expects(self::once())
            ->method('execute')
            ->with(PaymentMethodInterface::CANCEL, $cancelPaymentTransaction)
            ->will($responseValue);

        $this->paymentMethodProvider->expects(self::any())
            ->method('hasPaymentMethod')
            ->with($authorizationPaymentTransaction->getPaymentMethod())
            ->willReturn(true);

        $this->paymentMethodProvider->expects(self::any())
            ->method('getPaymentMethod')
            ->with($authorizationPaymentTransaction->getPaymentMethod())
            ->willReturn($paymentMethod);

        $this->paymentTransactionProvider->expects(self::exactly(2))
            ->method('savePaymentTransaction')
            ->withConsecutive(
                [$cancelPaymentTransaction],
                [$authorizationPaymentTransaction]
            );

        $this->contextAccessor->expects(self::once())
            ->method('setValue')
            ->with($context, $options['attribute'], $expected);

        $this->action->initialize($options);
        $this->action->execute($context);
    }

    public function executeDataProvider(): array
    {
        $paymentTransaction = new PaymentTransaction();
        $paymentTransaction->setPaymentMethod('testPaymentMethodType');

        return [
            'default' => [
                'data' => [
                    'cancelPaymentTransaction' => $paymentTransaction
                        ->setAction(PaymentMethodInterface::CANCEL),
                    'options' => [
                        'paymentTransaction' => $paymentTransaction,
                        'attribute' => new PropertyPath('test'),
                        'transactionOptions' => [
                            'testOption' => 'testOption',
                        ],
                    ],
                    'response' => ['testResponse' => 'testResponse'],
                ],
                'expected' => [
                    'transaction' => null,
                    'successful' => false,
                    'message' => 'oro.payment.message.error',
                    'testOption' => 'testOption',
                    'testResponse' => 'testResponse',
                ],
            ],
            'throw exception' => [
                'data' => [
                    'cancelPaymentTransaction' => $paymentTransaction
                        ->setAction(PaymentMethodInterface::CANCEL),
                    'options' => [
                        'paymentTransaction' => $paymentTransaction,
                        'attribute' => new PropertyPath('test'),
                        'transactionOptions' => [
                            'testOption' => 'testOption',
                        ],
                    ],
                    'response' => new \Exception(),
                ],
                'expected' => [
                    'transaction' => null,
                    'successful' => false,
                    'message' => 'oro.payment.message.error',
                    'testOption' => 'testOption',
                ],
            ],
        ];
    }

    /**
     * @dataProvider executeWrongOptionsDataProvider
     */
    public function testExecuteWrongOptions(array $options)
    {
        $this->expectException(UndefinedOptionsException::class);
        $paymentTransaction = new PaymentTransaction();
        $paymentTransaction->setPaymentMethod('testPaymentMethodType');

        $this->action->initialize($options);
        $this->action->execute([]);
    }

    public function executeWrongOptionsDataProvider(): array
    {
        return [
            [['someOption' => 'someValue']],
            [['object' => 'someValue']],
            [['amount' => 'someAmount']],
            [['currency' => 'someCurrency']],
            [['paymentMethod' => 'somePaymentMethod']],
        ];
    }

    /**
     * {@inheritDoc}
     */
    protected function getAction()
    {
        return new PaymentTransactionCancelAction(
            $this->contextAccessor,
            $this->paymentMethodProvider,
            $this->paymentTransactionProvider,
            $this->router
        );
    }

    public function testExecuteFailedWhenPaymentMethodNotExists()
    {
        $context = [];
        $options = [
            'paymentTransaction' => new PaymentTransaction(),
            'attribute' => new PropertyPath('test'),
            'transactionOptions' => [
                'testOption' => 'testOption',
            ],
        ];

        $this->paymentMethodProvider->expects(self::once())
            ->method('hasPaymentMethod')
            ->willReturn(false);
        $this->contextAccessor->expects(self::any())
            ->method('getValue')
            ->willReturnArgument(1);

        $this->contextAccessor->expects(self::once())
            ->method('setValue')
            ->with(
                $context,
                $options['attribute'],
                [
                    'transaction' => null,
                    'successful' => false,
                    'message' => 'oro.payment.message.error',
                    'testOption' => 'testOption',
                ]
            );

        $this->action->initialize($options);
        $this->action->execute($context);
    }
}
