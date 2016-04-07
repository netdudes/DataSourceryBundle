<?php
namespace Netdudes\DataSourceryBundle\Query;

use Netdudes\DataSourceryBundle\DataSource\Configuration\Field;
use Netdudes\DataSourceryBundle\UQL\Event\InterpreterEvents;
use Netdudes\DataSourceryBundle\UQL\Event\PreCreateFilterConditionEvent;
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
    public function create(Field $field, $method, $value)
    {
        $event = new PreCreateFilterConditionEvent($field->getDataType(), $value);
        $this->eventDispatcher->dispatch(
            InterpreterEvents::PRE_CREATE_FILTER_CONDITION,
            $event
        );
        $databaseValue = $event->getDatabaseValue();

        return new FilterCondition($field->getUniqueName(), $method, $value, $databaseValue);
    }
}
