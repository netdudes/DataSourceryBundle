<?php
namespace Netdudes\DataSourceryBundle\Query;

use Netdudes\DataSourceryBundle\DataSource\Configuration\Field;
use Netdudes\DataSourceryBundle\Query\Event\PreFilterConditionCreationEvent;
use Netdudes\DataSourceryBundle\Query\Event\FilterConditionEvents;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FilterConditionFactory
{
    /**
     * @var EventDispatcherInterface
     */
    protected $eventDispatcher;

    public function __construct()
    {
        $this->eventDispatcher = new EventDispatcher();
    }

    /**
     * @param EventSubscriberInterface $eventSubscriber
     */
    public function registerEventSubscriber(EventSubscriberInterface $eventSubscriber)
    {
        $this->eventDispatcher->addSubscriber($eventSubscriber);
    }

    /**
     * @param mixed  $value
     * @param string $method
     * @param Field  $field
     *
     * @return FilterCondition
     */
    public function create($value, $method, Field $field)
    {
        $event = new PreFilterConditionCreationEvent($value, $field->getDataType());
        $this->eventDispatcher->dispatch(
            FilterConditionEvents::PRE_CREATE,
            $event
        );

        return new FilterCondition($field->getUniqueName(), $method, $value, $event->getValue());
    }
}
