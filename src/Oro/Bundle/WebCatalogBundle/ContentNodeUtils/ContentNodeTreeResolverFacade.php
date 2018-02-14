<?php

namespace Oro\Bundle\WebCatalogBundle\ContentNodeUtils;

use Oro\Bundle\ScopeBundle\Entity\Scope;
use Oro\Bundle\WebCatalogBundle\Cache\Dumper\ContentNodeTreeDumper;
use Oro\Bundle\WebCatalogBundle\Entity\ContentNode;

class ContentNodeTreeResolverFacade implements ContentNodeTreeResolverInterface
{
    /**
     * @var ContentNodeTreeResolverInterface
     */
    private $cachedResolver;

    /**
     * @var ContentNodeTreeDumper
     */
    private $contentNodeTreeDumper;

    /**
     * @param ContentNodeTreeResolverInterface $cachedResolver
     * @param ContentNodeTreeDumper $contentNodeTreeDumper
     */
    public function __construct(
        ContentNodeTreeResolverInterface $cachedResolver,
        ContentNodeTreeDumper $contentNodeTreeDumper
    ) {
        $this->cachedResolver = $cachedResolver;
        $this->contentNodeTreeDumper = $contentNodeTreeDumper;
    }

    /**
     * {@inheritdoc}
     */
    public function supports(ContentNode $node, Scope $scope)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getResolvedContentNode(ContentNode $node, Scope $scope)
    {
        if (!$this->cachedResolver->supports($node, $scope)) {
            $this->contentNodeTreeDumper->dump($node, $scope);
        }

        return $this->cachedResolver->getResolvedContentNode($node, $scope);
    }
}
