<?php
namespace Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\QueryBuilder;

use Doctrine\ORM\QueryBuilder;
use Netdudes\DataSourceryBundle\Query\Sort;
use Netdudes\DataSourceryBundle\Query\SortCondition;

class Sorter
{
    /**
     * Apply the sortings to the query builder
     *
     * @param QueryBuilder $queryBuilder
     * @param Sort         $sortCollectionDefinition
     *
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function sort(QueryBuilder $queryBuilder, Sort $sortCollectionDefinition, $uniqueNameToQueryFieldMap)
    {
        /** @var $sortDefinition SortCondition */
        foreach ($sortCollectionDefinition as $sortDefinition) {
            $identifier = $uniqueNameToQueryFieldMap[$sortDefinition->getFieldName()];
            $queryBuilder->orderBy($identifier, $sortDefinition->getDirection());
        }

        return $queryBuilder;
    }
}
