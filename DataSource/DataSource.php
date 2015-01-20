<?php
namespace Netdudes\DataSourceryBundle\DataSource;

use Netdudes\DataSourceryBundle\DataSource\Configuration\Field;
use Netdudes\DataSourceryBundle\DataSource\Configuration\FieldInterface;
use Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\DoctrineDriver;
use Netdudes\DataSourceryBundle\DataSource\Driver\DriverInterface;
use Netdudes\DataSourceryBundle\Query\QueryInterface;
use Netdudes\DataSourceryBundle\Transformers\TransformerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DataSource implements DataSourceInterface
{
    /**
     * @var DoctrineDriver
     */
    protected $driver;

    /**
     * @var string
     */
    private $entityClass;

    /**
     * @var Field[]
     */
    private $fields;

    /**
     * @var TransformerInterface[]
     */
    private $transformers;

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @param                          $entityClass
     * @param                          $fields
     * @param                          $transformers
     * @param EventDispatcherInterface $eventDispatcher
     * @param DoctrineDriver           $doctrineDriver
     */
    public function __construct($entityClass, $fields, $transformers, EventDispatcherInterface $eventDispatcher, DriverInterface $doctrineDriver)
    {
        $this->entityClass = $entityClass;
        $this->fields = $fields;
        $this->transformers = $transformers;
        $this->eventDispatcher = $eventDispatcher;
        $this->driver = $doctrineDriver;
    }

    /**
     * Return the data after being processed according to the $query
     *
     * @param  QueryInterface $query
     *
     * @return array
     */
    public function getData(QueryInterface $query)
    {
        $selectFields = $this->calculateSelectFields($query);
        $query->setSelect($selectFields);
        $rows = $this->driver->getData($this, $query);

        return $this->applyTransformers($rows);
    }

    /**
     * Return the count of records returned by only filtering the data source,
     * before any pagination is done.
     *
     * @param QueryInterface $query
     *
     * @return integer
     */
    public function getRecordCount(QueryInterface $query)
    {
        return $this->driver->getRecordCount($this, $query);
    }

    /**
     * Return list of data source fields available for the data source
     *
     * @return FieldInterface
     */
    public function getFields()
    {
        return $this->fields;
    }

    public function getEntityClass()
    {
        return $this->entityClass;
    }

    public function getTransformers()
    {
        return $this->transformers;
    }

    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher()
    {
        return $this->eventDispatcher;
    }

    /**
     * @param $rows
     *
     * @return mixed
     */
    protected function applyTransformers($rows)
    {
        foreach ($this->transformers as $transformer) {
            foreach ($rows as $index => $row) {
                $rows[$index] = $transformer->transform($row, $this);
            }
        }

        return $rows;
    }

    /**
     * Build the select including the ones passed in the query plus the ones required by the
     * transformers.
     *
     * @param QueryInterface $query
     *
     * @return mixed
     */
    protected function calculateSelectFields(QueryInterface $query)
    {
        return array_reduce(
            $this->getTransformers(),
            function ($selectFields, TransformerInterface $transformer) {
                return array_unique(array_merge($selectFields, $transformer->getRequiredFieldNames()));
            },
            $query->getSelect()
        );
    }
}
