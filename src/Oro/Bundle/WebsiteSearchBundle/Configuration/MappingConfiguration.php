<?php

namespace Oro\Bundle\WebsiteSearchBundle\Configuration;

use Oro\Bundle\SearchBundle\Query\Query;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Provides schema for configuration that is loaded from "Resources/config/oro/website_search.yml" files.
 */
class MappingConfiguration implements ConfigurationInterface
{
    /** @var array */
    protected array $fieldTypes = [
        Query::TYPE_TEXT,
        Query::TYPE_DECIMAL,
        Query::TYPE_INTEGER,
        Query::TYPE_DATETIME,
    ];

    /**
     * Website search mapping configuration structure
     *
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('mappings');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->useAttributeAsKey('name')
            ->prototype('array')
            ->children()
                ->scalarNode('alias')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('fields')
                    ->isRequired()
                    ->requiresAtLeastOneElement()
                    ->useAttributeAsKey('name', false)
                    ->prototype('array')
                        ->children()
                            ->scalarNode('name')->end()
                            ->enumNode('type')
                                ->isRequired()
                                ->cannotBeEmpty()
                                ->values($this->fieldTypes)
                            ->end()
                            ->booleanNode('store')->end()
                            ->booleanNode('default_search_field')->end()
                            ->booleanNode('fulltext')->defaultTrue()->end()
                            ->scalarNode('organization_id')->defaultNull()->end()
                        ->end()
                        ->validate()
                            ->always(\Closure::fromCallable([$this, 'validateFieldMapping']))
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }

    /**
     * @param array $fieldMapping
     * @return array
     */
    protected function validateFieldMapping(array $fieldMapping): array
    {
        if ($fieldMapping['type'] !== Query::TYPE_TEXT) {
            $fieldMapping['fulltext'] = false;
        }

        return $fieldMapping;
    }
}
