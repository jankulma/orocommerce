<?php

namespace Oro\Bundle\WebCatalogBundle\Tests\Unit\Cache;

use Doctrine\Common\Collections\ArrayCollection;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\LocaleBundle\Entity\Localization;
use Oro\Bundle\LocaleBundle\Entity\LocalizedFallbackValue;
use Oro\Bundle\LocaleBundle\Model\FallbackType;
use Oro\Bundle\WebCatalogBundle\Cache\ContentNodeTreeCache;
use Oro\Bundle\WebCatalogBundle\Cache\ResolvedData\ResolvedContentNode;
use Oro\Bundle\WebCatalogBundle\Cache\ResolvedData\ResolvedContentVariant;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;
use Oro\Bundle\WebCatalogBundle\Entity\Repository\WebCatalogRepository;
use Oro\Bundle\WebCatalogBundle\Entity\WebCatalog;
use Oro\Component\Testing\Unit\EntityTrait;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class ContentNodeTreeCacheTest extends TestCase
{
    use EntityTrait;

    /** @var DoctrineHelper|\PHPUnit\Framework\MockObject\MockObject */
    private $doctrineHelper;

    /** @var CacheItemPoolInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $cache;

    /** @var CacheItemInterface|\PHPUnit\Framework\MockObject\MockObject */
    private $cacheItem;

    /** @var ContentNodeTreeCache */
    private $contentNodeTreeCache;

    protected function setUp(): void
    {
        $this->doctrineHelper = $this->createMock(DoctrineHelper::class);
        $this->cache = $this->createMock(CacheItemPoolInterface::class);
        $this->cacheItem = $this->createMock(CacheItemInterface::class);

        $this->contentNodeTreeCache = new ContentNodeTreeCache(
            $this->doctrineHelper,
            $this->cache
        );
    }

    public function testFetchWhenNoCachedData()
    {
        $this->cache->expects($this->once())
            ->method('getItem')
            ->with('node_2_scope_5')
            ->willReturn($this->cacheItem);
        $this->cacheItem->expects(self::once())
            ->method('isHit')
            ->willReturn(false);

        $this->assertFalse($this->contentNodeTreeCache->fetch(2, 5));
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testFetchWhenCachedDataExist()
    {
        $cacheData = [
            'id' => 1,
            'identifier' => 'root',
            'priority' => 1,
            'resolveVariantTitle' => true,
            'titles' => [
                ['string' => 'Title 1', 'localization' => null, 'fallback' => FallbackType::NONE],
                [
                    'string' => 'Title 1 EN',
                    'localization' => ['entity_class' => Localization::class, 'entity_id' => 5],
                    'fallback' => FallbackType::PARENT_LOCALIZATION
                ]
            ],
            'contentVariant' => [
                'data' => ['id' => 3, 'type' => 'test_type', 'test' => 1],
                'localizedUrls' => [
                    ['string' => '/test', 'localization' => null, 'fallback' => FallbackType::NONE]
                ]
            ],
            'childNodes' => [
                [
                    'id' => 2,
                    'priority' => 2,
                    'identifier' => 'root__second',
                    'resolveVariantTitle' => false,
                    'titles' => [
                        ['string' => 'Child Title 1', 'localization' => null, 'fallback' => FallbackType::NONE]
                    ],
                    'contentVariant' => [
                        'data' => ['id' => 7, 'type' => 'test_type', 'test' => 2],
                        'localizedUrls' => [
                            ['string' => '/test/content', 'localization' => null, 'fallback' => FallbackType::NONE]
                        ]
                    ],
                    'childNodes' => []
                ]
            ]
        ];
        $expected = new ResolvedContentNode(
            1,
            'root',
            1,
            new ArrayCollection(
                [
                    (new LocalizedFallbackValue())->setString('Title 1'),
                    (new LocalizedFallbackValue())
                        ->setString('Title 1 EN')
                        ->setFallback(FallbackType::PARENT_LOCALIZATION)
                        ->setLocalization($this->getEntity(Localization::class, ['id' => 5])),
                ]
            ),
            (new ResolvedContentVariant())
                ->setData(['id' => 3, 'type' => 'test_type', 'test' => 1])
                ->addLocalizedUrl((new LocalizedFallbackValue())->setString('/test')),
            true
        );

        $childResolvedNode = new ResolvedContentNode(
            2,
            'root__second',
            2,
            new ArrayCollection(
                [
                    (new LocalizedFallbackValue())->setString('Child Title 1')
                ]
            ),
            (new ResolvedContentVariant())
                ->setData(['id' => 7, 'type' => 'test_type', 'test' => 2])
                ->addLocalizedUrl((new LocalizedFallbackValue())->setString('/test/content')),
            false
        );

        $expected->addChildNode($childResolvedNode);

        $this->cache->expects($this->once())
            ->method('getItem')
            ->with('node_2_scope_5')
            ->willReturn($this->cacheItem);
        $this->cacheItem->expects(self::once())
            ->method('isHit')
            ->willReturn(true);
        $this->cacheItem->expects(self::exactly(2))
            ->method('get')
            ->willReturn($cacheData);

        $this->doctrineHelper->expects($this->any())
            ->method('getEntityReference')
            ->willReturnCallback(
                function ($className, $id) {
                    return $this->getEntity($className, ['id' => $id]);
                }
            );

        $this->assertEquals($expected, $this->contentNodeTreeCache->fetch(2, 5));
    }

    public function testShouldSaveEmptyCacheIfNodeNotResolved()
    {
        $this->cache->expects(self::once())
            ->method('getItem')
            ->with('node_2_scope_5')
            ->willReturn($this->cacheItem);
        $this->cacheItem->expects(self::once())
            ->method('set')
            ->with([]);
        $this->cache->expects(self::once())
            ->method('save')
            ->with($this->cacheItem);


        $this->contentNodeTreeCache->save(2, 5, null);
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function testShouldSaveCacheIfNodeResolved()
    {
        $resolvedNode = new ResolvedContentNode(
            1,
            'root',
            1,
            new ArrayCollection(
                [
                    (new LocalizedFallbackValue())->setString('Title 1'),
                    (new LocalizedFallbackValue())->setString('Title 1 EN')
                        ->setFallback(FallbackType::PARENT_LOCALIZATION)
                        ->setLocalization($this->getEntity(Localization::class, ['id' => 5])),
                ]
            ),
            (new ResolvedContentVariant())->setData(['id' => 3, 'type' => 'test_type', 'test' => 1])
                ->addLocalizedUrl((new LocalizedFallbackValue())->setString('/test')),
            true
        );

        $childResolveNode = new ResolvedContentNode(
            2,
            'root__second',
            2,
            new ArrayCollection([(new LocalizedFallbackValue())->setString('Child Title 1')]),
            (new ResolvedContentVariant())->addLocalizedUrl((new LocalizedFallbackValue())->setString('/test/c'))
                ->setData([
                    'id' => 7,
                    'type' => 'test_type',
                    'skipped_null' => null,
                    'sub_array' => ['a' => 'b'],
                    'sub_iterator' => new ArrayCollection(
                        ['c' => $this->getEntity(Localization::class, ['id' => 3])]
                    )
                ]),
            false
        );
        $resolvedNode->addChildNode($childResolveNode);
        $convertedNode = [
            'id' => $resolvedNode->getId(),
            'identifier' => $resolvedNode->getIdentifier(),
            'priority' => 1,
            'resolveVariantTitle' => true,
            'titles' => [
                ['string' => 'Title 1', 'localization' => null, 'fallback' => null],
                [
                    'string' => 'Title 1 EN',
                    'localization' => ['entity_class' => Localization::class, 'entity_id' => 5],
                    'fallback' => 'parent_localization',
                ],
            ],
            'contentVariant' => [
                'data' => ['id' => 3, 'type' => 'test_type', 'test' => 1],
                'localizedUrls' => [['string' => '/test', 'localization' => null, 'fallback' => null]]
            ],
            'childNodes' => [
                [
                    'id' => 2,
                    'identifier' => 'root__second',
                    'priority' => 2,
                    'resolveVariantTitle' => false,
                    'titles' => [['string' => 'Child Title 1', 'localization' => null, 'fallback' => null]],
                    'contentVariant' => [
                        'data' => [
                            'id' => 7,
                            'type' => 'test_type',
                            'sub_array' => ['a' => 'b'],
                            'sub_iterator' => ['c' => ['entity_class' => Localization::class, 'entity_id' => 3]]
                        ],
                        'localizedUrls' => [['string' => '/test/c', 'localization' => null, 'fallback' => null]]
                    ],
                    'childNodes' => [],
                ],
            ],
        ];

        $this->doctrineHelper->expects($this->any())->method('isManageableEntity')->willReturn(true);
        $this->doctrineHelper->expects($this->any())
            ->method('getEntityClass')
            ->willReturnCallback(
                function ($object) {
                    return get_class($object);
                }
            );
        $this->doctrineHelper->expects($this->any())->method('getSingleEntityIdentifier')
            ->willReturnCallback(
                function ($object) {
                    return $object->getId();
                }
            );

        $this->cache->expects(self::once())
            ->method('getItem')
            ->with('node_2_scope_5')
            ->willReturn($this->cacheItem);
        $this->cacheItem->expects(self::once())
            ->method('set')
            ->with($convertedNode);
        $this->cache->expects(self::once())
            ->method('save')
            ->with($this->cacheItem);

        $this->contentNodeTreeCache->save(2, 5, $resolvedNode);
    }

    public function testDeleteForNode()
    {
        $contentNodeId = 15;
        $node = $this->getEntity(ContentNode::class, ['id' => $contentNodeId]);

        $webCatalog = new WebCatalog();
        $node->setWebCatalog($webCatalog);

        $fooScopeIds = 42;
        $barScopeIds = 2;

        $webCatalogRepository = $this->createMock(WebCatalogRepository::class);
        $webCatalogRepository->expects($this->once())
            ->method('getUsedScopesIds')
            ->with($webCatalog)
            ->willReturn([$fooScopeIds, $barScopeIds]);

        $this->doctrineHelper->expects($this->once())
            ->method('getEntityRepositoryForClass')
            ->with(WebCatalog::class)
            ->willReturn($webCatalogRepository);

        $fooScopeCacheKey = "node_{$contentNodeId}_scope_{$fooScopeIds}";
        $barScopeCacheKey = "node_{$contentNodeId}_scope_{$barScopeIds}";

        $this->cache->expects($this->once())
            ->method('deleteItems')
            ->with([$fooScopeCacheKey, $barScopeCacheKey]);

        $this->contentNodeTreeCache->deleteForNode($node);
    }
}
