<?php

namespace Oro\Bundle\WebsiteSearchBundle\EventListener;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnClearEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Oro\Bundle\EntityBundle\ORM\DoctrineHelper;
use Oro\Bundle\FormBundle\Event\FormHandler\AfterFormProcessEvent;
use Oro\Bundle\PlatformBundle\EventListener\OptionalListenerInterface;
use Oro\Bundle\SearchBundle\Utils\IndexationEntitiesContainer;
use Oro\Bundle\WebsiteSearchBundle\Event\ReindexationRequestEvent;
use Oro\Bundle\WebsiteSearchBundle\Provider\WebsiteSearchMappingProvider;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Handles any changes of entities and trigger re-indexation.
 */
class IndexationRequestListener implements OptionalListenerInterface
{
    /**
     * @var bool
     */
    protected $enabled = true;

    /**
     * @var DoctrineHelper
     */
    protected $doctrineHelper;

    /**
     * @var WebsiteSearchMappingProvider
     */
    protected $mappingProvider;

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var IndexationEntitiesContainer
     */
    protected $changedEntities;

    /**
     * @param DoctrineHelper $doctrineHelper
     * @param WebsiteSearchMappingProvider $mappingProvider
     * @param EventDispatcherInterface $dispatcher
     * @param IndexationEntitiesContainer $changedEntities
     */
    public function __construct(
        DoctrineHelper $doctrineHelper,
        WebsiteSearchMappingProvider $mappingProvider,
        EventDispatcherInterface $dispatcher,
        IndexationEntitiesContainer $changedEntities
    ) {
        $this->doctrineHelper  = $doctrineHelper;
        $this->mappingProvider = $mappingProvider;
        $this->dispatcher      = $dispatcher;
        $this->changedEntities = $changedEntities;
    }

    /**
     * @param OnFlushEventArgs $args
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        if (!$this->enabled) {
            return;
        }

        $entityManager = $args->getEntityManager();
        $unitOfWork    = $entityManager->getUnitOfWork();

        foreach ($unitOfWork->getScheduledEntityUpdates() as $hash => $entity) {
            $className = $this->doctrineHelper->getEntityClass($entity);
            if (!$this->mappingProvider->hasFieldsMapping($className)) {
                continue;
            }
            $this->scheduleForSendingWithEvent($entity);
        }

        foreach ($unitOfWork->getScheduledEntityInsertions() as $updatedEntity) {
            if (!$this->mappingProvider->hasFieldsMapping(
                $this->doctrineHelper->getEntityClass($updatedEntity)
            )) {
                continue;
            }
            $this->scheduleForSendingWithEvent($updatedEntity);
        }

        // deleted entities should be processed as references because on postFlush they are already deleted
        $deletedEntities = $unitOfWork->getScheduledEntityDeletions();
        foreach ($deletedEntities as $hash => $entity) {
            if (!$this->mappingProvider->hasFieldsMapping(
                $this->doctrineHelper->getEntityClass($entity)
            )) {
                continue;
            }
            $this->scheduleDeletedEntityForSendingWithEvent($entityManager, $entity);
        }
    }

    /**
     * @param AfterFormProcessEvent $event
     */
    public function beforeEntityFlush(AfterFormProcessEvent $event)
    {
        if (!$this->enabled) {
            return;
        }

        $updatedEntity = $event->getData();
        if (!$this->mappingProvider->hasFieldsMapping(
            $this->doctrineHelper->getEntityClass($updatedEntity)
        )) {
            return;
        }

        $this->scheduleForSendingWithEvent($updatedEntity);
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled($enabled = true)
    {
        $this->enabled = $enabled;
    }

    /**
     * @param OnClearEventArgs $event
     */
    public function onClear(OnClearEventArgs $event)
    {
        if (!$event->getEntityClass()) {
            $this->changedEntities->clear();
        } else {
            $this->changedEntities->removeEntities($event->getEntityClass());
        }
    }

    /**
     * Post flush listening method
     */
    public function postFlush()
    {
        if (!$this->enabled) {
            return;
        }

        if ($this->changedEntities->getEntities()) {
            $this->triggerReindexationEvent();
        }
    }

    /**
     * Add an entity to a helper array before sending them with an ReindexationRequestEvent
     *
     * @param object $entity
     * @throws \InvalidArgumentException
     */
    protected function scheduleForSendingWithEvent($entity)
    {
        if (!(is_object($entity) && method_exists($entity, 'getId'))) {
            throw new \InvalidArgumentException('Entity must be an object with `getId` method.');
        }

        $this->changedEntities->addEntity($entity);
    }

    /**
     * @param EntityManager $entityManager
     * @param               $entity
     */
    protected function scheduleDeletedEntityForSendingWithEvent($entityManager, $entity)
    {
        $entityReference = $entityManager->getReference(
            $this->doctrineHelper->getEntityClass($entity),
            $this->doctrineHelper->getSingleEntityIdentifier($entity)
        );

        $this->scheduleForSendingWithEvent($entityReference);
    }

    /**
     * Trigger the event and clear the scheduled data
     */
    protected function triggerReindexationEvent()
    {
        foreach ($this->changedEntities->getEntities() as $class => $entities) {
            $ids = [];

            /**
             * @var object $entity
             */
            foreach ($entities as $entity) {
                $ids[] = $this->doctrineHelper->getSingleEntityIdentifier($entity);
            }

            $reindexationRequestEvent = new ReindexationRequestEvent(
                [$class],
                [],
                $ids
            );

            $this->dispatcher->dispatch(ReindexationRequestEvent::EVENT_NAME, $reindexationRequestEvent);
        }

        $this->changedEntities->clear();
    }
}
