<?php

namespace Oro\Bundle\CatalogBundle\Migrations\Schema\v1_11;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Type;
use Oro\Bundle\EntityConfigBundle\Config\Id\FieldConfigId;
use Oro\Bundle\EntityConfigBundle\Migration\UpdateEntityConfigFieldValueQuery;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;
use Psr\Log\LoggerInterface;

/**
 * Change value to `extra_lazy` for `fetch` option of relation settings between Category and Product entities.
 */
class UpdateCategoryProductRelationFetchModeQuery extends ParametrizedMigrationQuery
{
    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return 'Add fetch mode `extra_lazy` to OneToMany relation between Category and Product entities';
    }

    /**
     * {@inheritdoc}
     */
    public function execute(LoggerInterface $logger)
    {
        $rows = $this->createEntityConfigQb()
            ->execute()
            ->fetchAll(\PDO::FETCH_ASSOC);

        $this->process(reset($rows), $logger);
    }

    /**
     * {@inheritdoc}
     */
    private function process(array $row, LoggerInterface $logger)
    {
        $data = $this->connection->convertToPHPValue($row['data'], 'array');

        foreach ($data['extend']['relation'] as $key => $relation) {
            if ($relation['target_entity'] !== 'Oro\Bundle\ProductBundle\Entity\Product') {
                continue;
            }

            /** @var FieldConfigId $fieldConfig */
            $fieldConfig = $relation['field_id'];

            // update entity field config
            $query = new UpdateEntityConfigFieldValueQuery(
                $fieldConfig->getClassName(),
                $fieldConfig->getFieldName(),
                'extend',
                'fetch',
                'extra_lazy'
            );

            $query->setConnection($this->connection);
            $query->execute($logger);

            // update entity config
            $data['extend']['relation'][$key]['fetch'] = 'extra_lazy';
            $this->updateEntityConfigData($data, $row['id'], $logger);

            break;
        }
    }

    /**
     * @return QueryBuilder
     */
    private function createEntityConfigQb()
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('ec.id, ec.data')
            ->from('oro_entity_config', 'ec')
            ->where($qb->expr()->eq('ec.class_name', ':className'))
            ->setParameter('className', 'Oro\Bundle\CatalogBundle\Entity\Category')
            ->setMaxResults(1);

        return $qb;
    }

    /**
     * @param array $entityData
     * @param int $id
     * @param LoggerInterface $logger
     */
    private function updateEntityConfigData(array $entityData, $id, LoggerInterface $logger)
    {
        $query = 'UPDATE oro_entity_config SET data = ? WHERE id = ?';
        $parameters = [$this->connection->convertToDatabaseValue($entityData, Type::TARRAY), $id];

        $this->logQuery($logger, $query, $parameters);
        $this->connection->executeUpdate($query, $parameters);
    }
}
