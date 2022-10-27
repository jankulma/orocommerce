<?php

namespace Oro\Bundle\WebsiteSearchBundle\Tests\Unit\Engine;

use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\SearchBundle\Engine\IndexerInterface;
use Oro\Bundle\SearchBundle\Entity\Item;
use Oro\Bundle\SearchBundle\Provider\SearchMappingProvider;
use Oro\Bundle\WebsiteBundle\Entity\Repository\WebsiteRepository;
use Oro\Bundle\WebsiteBundle\Provider\WebsiteProviderInterface;
use Oro\Bundle\WebsiteSearchBundle\Async\Topic\WebsiteSearchDeleteTopic;
use Oro\Bundle\WebsiteSearchBundle\Async\Topic\WebsiteSearchReindexTopic;
use Oro\Bundle\WebsiteSearchBundle\Async\Topic\WebsiteSearchResetIndexTopic;
use Oro\Bundle\WebsiteSearchBundle\Async\Topic\WebsiteSearchSaveTopic;
use Oro\Bundle\WebsiteSearchBundle\Engine\AsyncIndexer;
use Oro\Bundle\WebsiteSearchBundle\Engine\AsyncMessaging\ReindexMessageGranularizer;
use Oro\Bundle\WebsiteSearchBundle\Engine\IndexerInputValidator;
use Oro\Component\MessageQueue\Client\MessageProducerInterface;

class AsyncIndexerTest extends \PHPUnit\Framework\TestCase
{
    private const WEBSITE_ID = 1;

    private AsyncIndexer $indexer;

    private MessageProducerInterface|\PHPUnit\Framework\MockObject\MockObject $messageProducer;

    private IndexerInterface|\PHPUnit\Framework\MockObject\MockObject $baseIndexer;

    protected function setUp(): void
    {
        $this->messageProducer = $this->createMock(MessageProducerInterface::class);

        $this->baseIndexer = $this->createMock(IndexerInterface::class);

        $websiteRepository = $this->createMock(WebsiteRepository::class);
        $websiteRepository->method('getWebsiteIdentifiers')
            ->willReturn([self::WEBSITE_ID]);

        $websiteProvider = $this->createMock(WebsiteProviderInterface::class);
        $websiteProvider
            ->expects(self::any())
            ->method('getWebsiteIds')
            ->willReturn([1]);
        $mappingProvider = $this->createMock(SearchMappingProvider::class);
        $mappingProvider
            ->expects(self::any())
            ->method('isClassSupported')
            ->willReturnCallback(fn ($class) => class_exists($class, true));

        $managerRegistry = $this->createMock(ManagerRegistry::class);

        $inputValidator = new IndexerInputValidator($websiteProvider, $mappingProvider, $managerRegistry);

        $this->granularizer = $this->createMock(ReindexMessageGranularizer::class);

        $this->indexer = new AsyncIndexer(
            $this->baseIndexer,
            $this->messageProducer,
            $inputValidator
        );
    }

    public function testSaveOne(): void
    {
        $entity = $this->createMock(Item::class);
        $entity->method('getId')
            ->willReturn(101);

        $context = ['test'];

        $expectedParams = [
            'entity' => [
                [
                    'class' => get_class($entity),
                    'id' => 101,
                ],
            ],
            'context' => [
                'test',
            ],
        ];

        $this->messageProducer->expects(self::atLeastOnce())
            ->method('send')
            ->with(WebsiteSearchSaveTopic::getName(), $expectedParams);

        $this->indexer->save($entity, $context);
    }

    public function testSaveMany(): void
    {
        $entity1 = $this->createMock(Item::class);
        $entity1->method('getId')
            ->willReturn(101);

        $entity2 = $this->createMock(Item::class);
        $entity2->method('getId')
            ->willReturn(102);

        $context = ['test'];

        $expectedParams = [
            'entity' => [
                [
                    'class' => get_class($entity1),
                    'id' => 101,
                ],
                [
                    'class' => get_class($entity2),
                    'id' => 102,
                ],
            ],
            'context' => [
                'test',
            ],
        ];

        $this->messageProducer->expects(self::atLeastOnce())
            ->method('send')
            ->with(WebsiteSearchSaveTopic::getName(), $expectedParams);

        $this->indexer->save([$entity1, $entity2], $context);
    }

    public function testDeleteOne(): void
    {
        $entity = $this->createMock(Item::class);
        $entity->method('getId')
            ->willReturn(101);

        $context = ['test'];

        $expectedParams = [
            'entity' => [
                [
                    'class' => get_class($entity),
                    'id' => 101,
                ],
            ],
            'context' => [
                'test',
            ],
        ];

        $this->messageProducer->expects(self::atLeastOnce())
            ->method('send')
            ->with(WebsiteSearchDeleteTopic::getName(), $expectedParams);

        $this->indexer->delete($entity, $context);
    }

    public function testDeleteMany(): void
    {
        $entity1 = $this->createMock(Item::class);
        $entity1->method('getId')
            ->willReturn(101);

        $entity2 = $this->createMock(Item::class);
        $entity2->method('getId')
            ->willReturn(102);

        $context = ['test'];

        $expectedParams = [
            'entity' => [
                [
                    'class' => get_class($entity1),
                    'id' => 101,
                ],
                [
                    'class' => get_class($entity2),
                    'id' => 102,
                ],
            ],
            'context' => [
                'test',
            ],
        ];

        $this->messageProducer->expects(self::atLeastOnce())
            ->method('send')
            ->with(WebsiteSearchDeleteTopic::getName(), $expectedParams);

        $this->indexer->delete([$entity1, $entity2], $context);
    }

    public function testGetClassesForReindex(): void
    {
        $class = '\StdClass';
        $context = ['foo', 'bar'];

        $this->baseIndexer->expects(self::once())
            ->method('getClassesForReindex')
            ->with($class, $context);

        $this->indexer->getClassesForReindex($class, $context);
    }

    public function testResetReindex(): void
    {
        $context = ['test'];

        $expectedParams = [
            'class' => Item::class,
            'context' => [
                'test',
            ],
        ];

        $this->messageProducer->expects(self::atLeastOnce())
            ->method('send')
            ->with(WebsiteSearchResetIndexTopic::getName(), $expectedParams);

        $this->indexer->resetIndex(Item::class, $context);
    }

    public function testReindex(): void
    {
        $expectedParams = [
            'class' => [Item::class],
            'context' => ['websiteIds' => [1]],
            'granulize' => true,
        ];

        $this->messageProducer
            ->expects(self::atLeastOnce())
            ->method('send')
            ->with(WebsiteSearchReindexTopic::getName(), $expectedParams);

        $this->indexer->reindex(Item::class, []);
    }
}
