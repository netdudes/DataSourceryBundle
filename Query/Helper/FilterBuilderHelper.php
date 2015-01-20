<?php
namespace Netdudes\DataSourceryBundle\Query\Helper;

use Netdudes\DataSourceryBundle\DataSource\DataSourceInterface;
use Netdudes\DataSourceryBundle\Query\Filter;

class FilterBuilderHelper
{
    /**
     * @param \Netdudes\DataSourceryBundle\DataSource\DataSourceInterface $dataSource
     * @param                     $searchTerm
     *
     * @return array|Filter
     */
    public function buildFullTextSearchFilter(DataSourceInterface $dataSource, $searchTerm)
    {
        // TODO: This functionality should be implemented here instead of in a static method in the filter object.
        return Filter::createFromFullTextSearch($dataSource, $searchTerm);
    }
}
