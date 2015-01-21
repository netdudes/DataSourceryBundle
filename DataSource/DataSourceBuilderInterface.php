<?php
namespace Netdudes\DataSourceryBundle\DataSource;

use Netdudes\DataSourceryBundle\Transformers\TransformerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

interface DataSourceBuilderInterface
{
    public function addField($name, $type, $field, array $options = []);

    public function addNativeField($name, $type, $alias, array $options = []);

    public function addVectorField($name, $type, $filteringField, array $aliasedFields, array $options = []);

    public function removeField($name);

    public function addTransformer(TransformerInterface $transformer);

    public function addEventListener($eventName, $listener);

    public function addEventSubscriber(EventSubscriberInterface $eventSubscriber);

    /**
     * @return DataSourceInterface
     */
    public function build();
}
