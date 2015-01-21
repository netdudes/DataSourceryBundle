<?php

namespace Netdudes\DataSourceryBundle\Query;

interface QueryInterface
{
    /**
     * @return Filter
     */
    public function getFilter();

    /**
     * @param Filter $filterDefinition
     */
    public function setFilter(Filter $filterDefinition);

    /**
     * @return Pagination
     */
    public function getPagination();

    /**
     * @param Pagination $paginationDefinition
     */
    public function setPagination(Pagination $paginationDefinition);

    /**
     * @return array
     */
    public function getSelect();

    /**
     * @param array $elements
     */
    public function setSelect(array $elements);

    /**
     * @return Sort
     */
    public function getSort();

    /**
     * @param Sort $sortDefinition
     */
    public function setSort(Sort $sortDefinition);

    /**
     * @param SortCondition $sortDefinition
     */
    public function addSortCondition(SortCondition $sortDefinition);
}
