<?php
namespace Netdudes\DataSourceryBundle\DataSource;

use Netdudes\DataSourceryBundle\DataSource\Configuration\Field;
use Netdudes\DataSourceryBundle\DataSource\Exception\InvalidDataTypeException;
use Netdudes\DataSourceryBundle\DataSource\Util\ChoicesBuilder;
use Netdudes\DataSourceryBundle\DataType\BooleanDataType;
use Netdudes\DataSourceryBundle\DataType\DateDataType;
use Netdudes\DataSourceryBundle\DataType\EntityDataType;
use Netdudes\DataSourceryBundle\DataType\NumberDataType;
use Netdudes\DataSourceryBundle\DataType\PercentDataType;
use Netdudes\DataSourceryBundle\DataType\SearchTextDataType;
use Netdudes\DataSourceryBundle\DataType\StringDataType;
use Netdudes\DataSourceryBundle\Transformers\TransformerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class DataSourceBuilder implements DataSourceBuilderInterface
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var Field[]
     */
    private $fields = [];

    /**
     * @var TransformerInterface[]
     */
    private $transformers = [];

    /**
     * @var Field[]
     */
    private $nativeFields = [];

    /**
     * @var string
     */
    private $entityClass;

    /**
     * @var ChoicesBuilder
     */
    private $choicesBuilder;

    /**
     * @var DataSourceFactoryInterface
     */
    private $dataSourceFactory;

    /**
     * @param string                     $entityClass
     * @param EventDispatcherInterface   $eventDispatcher
     * @param DataSourceFactoryInterface $dataSourceFactory
     * @param ChoicesBuilder             $choicesBuilder
     */
    public function __construct($entityClass, EventDispatcherInterface $eventDispatcher, DataSourceFactoryInterface $dataSourceFactory, ChoicesBuilder $choicesBuilder)
    {
        $this->entityClass = $entityClass;
        $this->eventDispatcher = $eventDispatcher;
        $this->choicesBuilder = $choicesBuilder;
        $this->dataSourceFactory = $dataSourceFactory;
    }

    public function addField($name, $type, $field, array $options = [])
    {
        $this->fields[] = $this->newField($name, $type, $field, $options);

        return $this;
    }

    public function addNativeField($name, $type, $alias, array $options = [])
    {
        $this->nativeFields[] = $this->newNativeField($name, $type, $alias, $options);

        return $this;
    }

    public function addVectorField($name, $type, $filteringField, array $aliasedFields, array $options = [])
    {
        $this->fields[] = $this->newRawField($name, $type, $filteringField, $aliasedFields, $options);

        return $this;
    }

    public function addSearchField($name, $type)
    {
        $this->fields[] = $this->newRawField($name, $type, null, null, []);

        return $this;
    }

    public function removeField($name)
    {
        foreach ($this->fields as $k => $field) {
            if ($field->getUniqueName() == $name) {
                unset($this->fields[$k]);
                break;
            }
        }

        return $this;
    }

    public function addTransformer(TransformerInterface $transformer)
    {
        $this->transformers[] = $transformer;

        return $this;
    }

    public function addEventListener($eventName, $listener)
    {
        $this->eventDispatcher->addListener($eventName, $listener);

        return $this;
    }

    public function addEventSubscriber(EventSubscriberInterface $eventSubscriber)
    {
        $this->eventDispatcher->addSubscriber($eventSubscriber);

        return $this;
    }

    public function build()
    {
        return $this->dataSourceFactory->create(
            $this->entityClass,
            array_merge($this->fields, $this->nativeFields),
            $this->transformers,
            $this->eventDispatcher
        );
    }

    private function newField($name, $type, $field, $options)
    {
        return $this->newRawField($name, $type, $field, null, $options);
    }

    private function getOption($options, $key, $default)
    {
        if (array_key_exists($key, $options)) {
            return $options[$key];
        }

        return $default;
    }

    /**
     * Helper function: matches a data type name given in the configuration to the needed
     * DataType instance.
     *
     * @param $type
     *
     * @throws InvalidDataTypeException
     *
     * @return mixed
     */
    private function getDataTypeByName($type)
    {
        switch ($type) {
            case 'date':
                return new DateDataType();
            case 'string':
                return new StringDataType();
            case 'number':
                return new NumberDataType();
            case 'boolean':
                return new BooleanDataType();
            case 'entity':
                return new EntityDataType();
            case 'percent':
                return new PercentDataType();
            case 'search_text':
                return new SearchTextDataType();
            default:
                throw new InvalidDataTypeException();
        }
    }

    private function newNativeField($name, $type, $alias, $options)
    {
        return $this->newRawField($name, $type, null, $alias, $options);
    }

    private function newRawField($name, $type, $field, $alias, $options)
    {
        $readable = $this->getOption($options, 'readable', $name);
        $description = $this->getOption($options, 'description', "");
        $choices = $this->getOption($options, 'choices', null);
        if ($choices) {
            $choices = $this->parseChoices($choices);
        }

        $type = $this->getDataTypeByName($type);

        return new Field($name, $readable, $description, $type, $field, $alias, $choices);
    }

    private function parseChoices($choices)
    {
        return $this->choicesBuilder->build($choices);
    }
}
