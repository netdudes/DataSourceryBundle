<?php
namespace Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\QueryBuilder;

use Doctrine\ORM\QueryBuilder;
use Netdudes\DataSourceryBundle\Query\Pagination;

class Paginator
{
    /**
     * Apply the pagination to the query builder
     *
     * @param QueryBuilder $queryBuilder
     * @param Pagination   $paginationDefinition
     *
     * @return QueryBuilder
     */
    public function paginate(QueryBuilder $queryBuilder, Pagination $paginationDefinition)
    {
        $queryBuilder->setFirstResult($paginationDefinition->getOffset());
        $queryBuilder->setMaxResults($paginationDefinition->getCount());

        return $queryBuilder;
    }
}
