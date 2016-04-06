<?php
namespace Netdudes\DataSourceryBundle\DataSource;

use Netdudes\DataSourceryBundle\Transformers\TransformerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

interface DataSourceBuilderInterface
{
    /**
     * @param string $name
     * @param string $type
     * @param string $field
     * @param array  $options
     *
     * @return DataSourceBuilderInterface
     */
    public function addField($name, $type, $field, array $options = []);

    /**
     * @param string $name
     * @param string $type
     * @param string $alias
     * @param array  $options
     *
     * @return DataSourceBuilderInterface
     */
    public function addNativeField($name, $type, $alias, array $options = []);

    /**
     * @param string $name
     * @param string $type
     * @param string $filteringField
     * @param array  $aliasedFields
     * @param array  $options
     *
     * @return DataSourceBuilderInterface
     */
    public function addVectorField($name, $type, $filteringField, array $aliasedFields, array $options = []);

    /**
     * @param string $name
     * @param string $type
     *
     * @return DataSourceBuilderInterface
     */
    public function addSearchField($name, $type);

    /**
     * @param string $name
     *
     * @return DataSourceBuilderInterface
     */
    public function removeField($name);

    /**
     * @param TransformerInterface $transformer
     *
     * @return DataSourceBuilderInterface
     */
    public function addTransformer(TransformerInterface $transformer);

    /**
     * @param string   $eventName
     * @param callable $listener
     *
     * @return DataSourceBuilderInterface
     */
    public function addEventListener($eventName, $listener);

    /**
     * @param EventSubscriberInterface $eventSubscriber
     *
     * @return DataSourceBuilderInterface
     */
    public function addEventSubscriber(EventSubscriberInterface $eventSubscriber);

    /**
     * @return DataSourceInterface
     */
    public function build();
}
