<?php
namespace Netdudes\DataSourceryBundle\DataSource\Driver;

use Netdudes\DataSourceryBundle\DataSource\DataSourceInterface;
use Netdudes\DataSourceryBundle\Query\QueryInterface;

interface DriverInterface
{
    /**
     * @param DataSourceInterface $dataSource
     * @param QueryInterface      $query
     *
     * @return mixed
     */
    public function getData(DataSourceInterface $dataSource, QueryInterface $query);

    /**
     * @param DataSourceInterface $dataSource
     * @param QueryInterface      $query
     *
     * @return mixed
     */
    public function getRecordCount(DataSourceInterface $dataSource, QueryInterface $query);
}
