<?php

namespace Oro\Bundle\WebsiteSearchBundle\Query;

use Doctrine\Common\Collections\Expr\Expression;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

use Oro\Bundle\SearchBundle\Query\AbstractSearchQuery;
use Oro\Bundle\WebsiteSearchBundle\Event\SelectDataFromSearchIndexEvent;
use Oro\Bundle\SearchBundle\Engine\EngineV2Interface;
use Oro\Bundle\SearchBundle\Query\Query;

class WebsiteSearchQuery extends AbstractSearchQuery
{
    /** @var EngineV2Interface */
    protected $engine;

    /** @var EventDispatcherInterface */
    protected $dispatcher;

    /**
     * @param EventDispatcherInterface $eventDispatcher
     * @param EngineV2Interface        $engine
     * @param Query                    $query
     */
    public function __construct(
        EngineV2Interface $engine,
        EventDispatcherInterface $eventDispatcher,
        Query $query
    ) {
        $this->engine     = $engine;
        $this->dispatcher = $eventDispatcher;
        $this->query      = $query;
    }

    /**
     * @param Expression $expression
     * @param string     $type
     * @return $this
     */
    public function addWhere(Expression $expression, $type = self::WHERE_AND)
    {
        if (self::WHERE_AND === $type) {
            $this->query->getCriteria()->andWhere($expression);
        } elseif (self::WHERE_OR === $type) {
            $this->query->getCriteria()->orWhere($expression);
        }

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function query()
    {
        // EVENT: allow additional fields to be selected
        // by custom bundles
        $event = new SelectDataFromSearchIndexEvent(
            $this->query->getSelect()
        );
        $this->dispatcher->dispatch(SelectDataFromSearchIndexEvent::EVENT_NAME, $event);
        $this->query->select($event->getSelectedData());

        return $this->engine->search($this->query);
    }
}
