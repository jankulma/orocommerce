<?php

namespace Oro\Bundle\ShippingBundle\CacheWarmer;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Types;
use Doctrine\Persistence\ManagerRegistry;
use Oro\Bundle\ActivityBundle\EntityConfig\ActivityScope;
use Oro\Bundle\ActivityListBundle\Entity\ActivityList;
use Oro\Bundle\ActivityListBundle\Tools\ActivityListEntityConfigDumperExtension;
use Oro\Bundle\EntityBundle\Tools\SafeDatabaseChecker;
use Oro\Bundle\EntityConfigBundle\Migration\RemoveManyToManyRelationQuery;
use Oro\Bundle\EntityConfigBundle\Migration\RemoveManyToOneRelationQuery;
use Oro\Bundle\EntityExtendBundle\Tools\ExtendHelper;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedMigrationQuery;
use Oro\Bundle\MigrationBundle\Migration\ParametrizedSqlMigrationQuery;
use Oro\Bundle\NoteBundle\Entity\Note;
use Psr\Log\LoggerInterface;

/**
 * Ensures that extend entity cache can be built after the removal of shipping rule-related entities.
 */
class EntityConfigRelationsMigration
{
    /**
     * @internal
     */
    const SHIPPING_RULE_CLASS_NAME = 'Oro\Bundle\ShippingBundle\Entity\ShippingRule';
    const SHIPPING_RULE_METHOD_CONFIG_CLASS_NAME = 'Oro\Bundle\ShippingBundle\Entity\ShippingRuleMethodConfig';
    const SHIPPING_RULE_METHOD_TYPE_CONFIG_CLASS_NAME = 'Oro\Bundle\ShippingBundle\Entity\ShippingRuleMethodTypeConfig';
    const SHIPPING_RULE_DESTINATION_CLASS_NAME = 'Oro\Bundle\ShippingBundle\Entity\ShippingRuleDestination';

    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var bool
     */
    private $applicationInstalled;

    /**
     * @param ManagerRegistry $managerRegistry
     * @param LoggerInterface $logger
     * @param bool $applicationInstalled
     */
    public function __construct(ManagerRegistry $managerRegistry, LoggerInterface $logger, $applicationInstalled)
    {
        $this->managerRegistry = $managerRegistry;
        $this->logger = $logger;
        $this->applicationInstalled = (bool)$applicationInstalled;
    }

    public function migrate()
    {
        if (!$this->applicationInstalled) {
            return;
        }

        if (class_exists(static::SHIPPING_RULE_CLASS_NAME)) {
            return;
        }

        /** @var Connection $configConnection */
        $configConnection = $this->managerRegistry->getConnection('config');

        if (!SafeDatabaseChecker::tablesExist($configConnection, 'oro_entity_config')) {
            return;
        }

        $this->removeNoteRelationBeforeUpdateAssociationKind($configConnection);
        $this->removeNoteRelationAfterUpdateAssociationKind($configConnection);

        $this->removeActivityListRelation($configConnection);

        $this->removeEntityFromEntityConfig($configConnection, self::SHIPPING_RULE_CLASS_NAME);
        $this->removeEntityFromEntityConfig($configConnection, self::SHIPPING_RULE_METHOD_CONFIG_CLASS_NAME);
        $this->removeEntityFromEntityConfig($configConnection, self::SHIPPING_RULE_METHOD_TYPE_CONFIG_CLASS_NAME);
        $this->removeEntityFromEntityConfig($configConnection, self::SHIPPING_RULE_DESTINATION_CLASS_NAME);
    }

    /**
     * @param Connection $configConnection
     */
    private function removeNoteRelationBeforeUpdateAssociationKind(Connection $configConnection)
    {
        $associationName = ExtendHelper::buildAssociationName(static::SHIPPING_RULE_CLASS_NAME);
        $this->executeUpdateRelationsQuery(
            new RemoveManyToOneRelationQuery(Note::class, $associationName),
            $configConnection
        );
    }

    /**
     * @param Connection $configConnection
     */
    private function removeNoteRelationAfterUpdateAssociationKind(Connection $configConnection)
    {
        $associationName = ExtendHelper::buildAssociationName(
            static::SHIPPING_RULE_CLASS_NAME,
            ActivityScope::ASSOCIATION_KIND
        );
        $this->executeUpdateRelationsQuery(
            new RemoveManyToManyRelationQuery(Note::class, $associationName),
            $configConnection
        );
    }

    /**
     * @param Connection $configConnection
     */
    private function removeActivityListRelation(Connection $configConnection)
    {
        $associationName = ExtendHelper::buildAssociationName(
            static::SHIPPING_RULE_CLASS_NAME,
            ActivityListEntityConfigDumperExtension::ASSOCIATION_KIND
        );
        $this->executeUpdateRelationsQuery(
            new RemoveManyToManyRelationQuery(ActivityList::class, $associationName),
            $configConnection
        );
    }

    /**
     * @param Connection $configConnection
     * @param string $className
     */
    private function removeEntityFromEntityConfig(Connection $configConnection, $className)
    {
        $this->executeUpdateRelationsQuery(
            new ParametrizedSqlMigrationQuery(
                'DELETE FROM oro_entity_config WHERE class_name = :class_name',
                ['class_name' => $className],
                ['class_name' => Types::STRING]
            ),
            $configConnection
        );
    }

    /**
     * @param ParametrizedMigrationQuery $query
     * @param Connection $connection
     */
    private function executeUpdateRelationsQuery(ParametrizedMigrationQuery $query, Connection $connection)
    {
        $query->setConnection($connection);
        $query->execute($this->logger);
    }
}
