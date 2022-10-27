<?php

namespace Oro\Bundle\ProductBundle\Tests\Unit\Async;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Oro\Bundle\ProductBundle\Async\ReindexProductsByAttributesProcessor;
use Oro\Bundle\ProductBundle\Async\Topics;
use Oro\Bundle\ProductBundle\Entity\Product;
use Oro\Bundle\ProductBundle\Entity\Repository\ProductRepository;
use Oro\Bundle\WebsiteSearchBundle\Event\ReindexationRequestEvent;
use Oro\Component\MessageQueue\Consumption\MessageProcessorInterface;
use Oro\Component\MessageQueue\Job\Job;
use Oro\Component\MessageQueue\Test\JobRunner;
use Oro\Component\MessageQueue\Transport\Message;
use Oro\Component\MessageQueue\Transport\MessageInterface;
use Oro\Component\MessageQueue\Transport\SessionInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class ReindexProductsByAttributesProcessorTest extends \PHPUnit\Framework\TestCase
{
    /** @var JobRunner|\PHPUnit\Framework\MockObject\MockObject */
    private $jobRunner;

    /** @var LoggerInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $logger;

    /** @var ProductRepository|\PHPUnit\Framework\MockObject\MockObject */
    private $repository;

    /** @var EventDispatcherInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $dispatcher;

    /** @var ManagerRegistry|\PHPUnit\Framework\MockObject\MockObject */
    private $registry;

    /** @var SessionInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $session;

    /** @var ReindexProductsByAttributesProcessor */
    private $processor;

    protected function setUp(): void
    {
        $this->jobRunner = $this->createMock(JobRunner::class);
        $this->registry = $this->createMock(ManagerRegistry::class);
        $this->dispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->session = $this->createMock(SessionInterface::class);
        $this->repository = $this->createMock(ProductRepository::class);

        $this->processor = new ReindexProductsByAttributesProcessor(
            $this->jobRunner,
            $this->registry,
            $this->dispatcher,
            $this->logger
        );
    }

    public function testGetSubscribedTopics()
    {
        $this->assertEquals(
            [Topics::REINDEX_PRODUCTS_BY_ATTRIBUTES],
            $this->processor->getSubscribedTopics()
        );
    }

    public function testProcessWhenMessageIsInvalid()
    {
        $messageBody = ['some body item'];
        $message = $this->getMessage($messageBody);

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Unexpected exception occurred during queue message processing');

        $result = $this->processor->process($message, $this->session);
        $this->assertEquals(MessageProcessorInterface::REJECT, $result);
    }

    public function testProcessWhenUnexpectedExceptionOccurred()
    {
        $messageBody = ['attributeIds' => [1, 2]];
        $message = $this->getMessage($messageBody);

        $this->jobRunner->expects($this->once())
            ->method('runUnique')
            ->willThrowException(new \Exception());

        $this->logger->expects($this->once())
            ->method('error')
            ->with('Unexpected exception occurred during queue message processing');

        $result = $this->processor->process($message, $this->session);
        $this->assertEquals(MessageProcessorInterface::REJECT, $result);
    }

    /**
     * @param array $productIds
     * @param \PHPUnit\Framework\MockObject\Rule\InvokedCount $dispatchExpected
     *
     * @dataProvider getProductIds
     */
    public function testProcess($productIds, $dispatchExpected)
    {
        $attributeIds = [1, 2];
        $messageBody = ['attributeIds' => $attributeIds];
        $message = $this->getMessage($messageBody);

        $this->mockRunUniqueJob();

        $this->repository->expects($this->once())
            ->method('getProductIdsByAttributesId')
            ->with($attributeIds)
            ->willReturn($productIds);

        $manager = $this->createMock(ObjectManager::class);
        $manager->expects($this->any())
            ->method('getRepository')
            ->with(Product::class)
            ->willReturn($this->repository);
        $this->registry->expects($this->any())
            ->method('getManagerForClass')
            ->with(Product::class)
            ->willReturn($manager);

        $this->dispatcher->expects($dispatchExpected)
            ->method('dispatch')
            ->with(
                new ReindexationRequestEvent([Product::class], [], $productIds, true, ['main']),
                ReindexationRequestEvent::EVENT_NAME
            );

        $result = $this->processor->process($message, $this->session);
        $this->assertEquals(MessageProcessorInterface::ACK, $result);
    }

    public function testProcessWithExceptionDuringReindexEventDispatching()
    {
        $attributeIds = [1, 2];
        $messageBody = ['attributeIds' => $attributeIds];
        $message = $this->getMessage($messageBody);

        $this->mockRunUniqueJob();

        $this->repository->expects($this->once())
            ->method('getProductIdsByAttributesId')
            ->with($attributeIds)
            ->willReturn([1]);

        $manager = $this->createMock(ObjectManager::class);
        $manager->expects($this->any())
            ->method('getRepository')
            ->with(Product::class)
            ->willReturn($this->repository);
        $this->registry->expects($this->any())
            ->method('getManagerForClass')
            ->with(Product::class)
            ->willReturn($manager);

        $exception = new \Exception();
        $this->dispatcher->expects($this->once())
            ->method('dispatch')
            ->willThrowException($exception);

        $this->logger->expects($this->once())
            ->method('error')
            ->with(
                'Unexpected exception occurred during triggering update of search index ',
                [
                    'exception' => $exception,
                    'topic' => Topics::REINDEX_PRODUCTS_BY_ATTRIBUTES
                ]
            );

        $result = $this->processor->process($message, $this->session);
        $this->assertEquals(MessageProcessorInterface::REJECT, $result);
    }

    public function getProductIds(): array
    {
        return [
            'empty array' => [
                'productIds' => [],
                'dispatchExpected' => $this->never()
            ],
            'array with id' => [
                'productIds' => [100, 101, 102],
                'dispatchExpected' => $this->once()
            ]
        ];
    }

    private function getMessage(array $body = []): MessageInterface
    {
        $message = new Message();
        $message->setBody($body);
        $message->setMessageId('msg-1');

        return $message;
    }

    private function mockRunUniqueJob(): void
    {
        $job = new Job();
        $job->setId(1);

        $childJob = new Job();
        $childJob->setId(2);
        $childJob->setRootJob($job);
        $childJob->setName(Topics::REINDEX_PRODUCTS_BY_ATTRIBUTES);

        $this->jobRunner->expects($this->once())
            ->method('runUnique')
            ->with('msg-1', Topics::REINDEX_PRODUCTS_BY_ATTRIBUTES)
            ->willReturnCallback(function ($jobId, $name, $callback) use ($childJob) {
                $this->assertEquals('msg-1', $jobId);
                $this->assertEquals(Topics::REINDEX_PRODUCTS_BY_ATTRIBUTES, $name);

                return $callback($this->jobRunner, $childJob);
            });
    }
}
