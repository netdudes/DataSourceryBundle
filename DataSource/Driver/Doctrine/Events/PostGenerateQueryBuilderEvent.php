<?php
namespace Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\Events;

use Doctrine\ORM\QueryBuilder;
use Symfony\Component\EventDispatcher\Event;

class PostGenerateQueryBuilderEvent extends Event
{
    /**
     * @var QueryBuilder
     */
    public $queryBuilder;

    /**
     * @var
     */
    public $fromAlias;

    /**
     * @param QueryBuilder $queryBuilder
     * @param              $fromAlias
     */
    public function __construct(QueryBuilder $queryBuilder, $fromAlias)
    {
        $this->queryBuilder = $queryBuilder;
        $this->fromAlias = $fromAlias;
    }
}
