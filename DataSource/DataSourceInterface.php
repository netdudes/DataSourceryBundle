<?php

namespace Netdudes\DataSourceryBundle\DataSource;

use Netdudes\DataSourceryBundle\DataSource\Configuration\FieldInterface;
use Netdudes\DataSourceryBundle\Query\QueryInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Minimal interface for a data source
 */
interface DataSourceInterface
{
    /**
     * Return the data after being processed according to the $query
     *
     * @param  QueryInterface $query
     *
     * @return array
     */
    public function getData(QueryInterface $query);

    /**
     * Return the count of records returned by only filtering the data source,
     * before any pagination is done.
     *
     * @param QueryInterface $query
     *
     * @return integer
     */
    public function getRecordCount(QueryInterface $query);

    /**
     * Return list of data source fields available for the data source
     *
     * @return FieldInterface
     */
    public function getFields();

    /**
     * Class of the model in the application targeted by this data source
     *
     * @return string
     */
    public function getEntityClass();

    /**
     * Transformers to be applied to each row after fetching
     *
     * @return mixed
     */
    public function getTransformers();

    /**
     * @return EventDispatcherInterface
     */
    public function getEventDispatcher();
}
