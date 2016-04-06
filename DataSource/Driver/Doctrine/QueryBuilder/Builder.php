<?php
namespace Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\QueryBuilder;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Netdudes\DataSourceryBundle\DataSource\DataSourceInterface;
use Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\DoctrineDriver;
use Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\Events\GenerateJoinsEvent;
use Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\Events\GenerateSelectsEvent;
use Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\Events\PostGenerateQueryBuilderEvent;
use Netdudes\DataSourceryBundle\Query\Query;
use Netdudes\DataSourceryBundle\Query\SearchTextFilterReducer;

class Builder
{
    /**
     * @var Filterer
     */
    protected $filterer;

    /**
     * @var Sorter
     */
    protected $sorter;

    /**
     * @var Paginator
     */
    protected $paginator;

    /**
     * @var string
     */
    protected $fromAlias;

    /**
     * @var array
     */
    protected $joins;

    /**
     * @var array
     */
    protected $selectFieldsMap = [];

    /**
     * @var RequiredFieldsExtractor
     */
    protected $requiredFieldsExtractor;

    /**
     * @var JoinGenerator
     */
    protected $joinGenerator;

    /**
     * @var SelectGenerator
     */
    protected $selectGenerator;

    /**
     * @var SearchTextFilterReducer
     */
    protected $searchTextFilterReducer;

    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @var DataSourceInterface
     */
    private $dataSource;

    /**
     * @param DataSourceInterface $dataSource
     * @param EntityManager       $entityManager
     */
    public function __construct(DataSourceInterface $dataSource, EntityManager $entityManager)
    {
        $this->dataSource = $dataSource;
        $this->entityManager = $entityManager;

        $fields = $dataSource->getFields();
        $transformers = $dataSource->getTransformers();

        $this->requiredFieldsExtractor = new RequiredFieldsExtractor($fields, $transformers);
        $this->joinGenerator = new JoinGenerator($fields, $this->getFromAlias(), $this->requiredFieldsExtractor);
        $this->selectGenerator = new SelectGenerator($fields, $this->getFromAlias(), $this->joinGenerator, $this->requiredFieldsExtractor);
        $this->filterer = new Filterer();
        $this->searchTextFilterReducer = new SearchTextFilterReducer($fields);
        $this->sorter = new Sorter();
        $this->paginator = new Paginator();
    }

    /**
     * Gets the fully generated query builder. Will autogenerate select and
     * join statements as needed.
     *
     * This function is cached, and will only be generated once per execution.
     *
     * @param Query $query
     *
     * @return QueryBuilder
     */
    public function buildQueryBuilder(Query $query, $entityClass)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();

        $queryBuilder->from($entityClass, $this->getFromAlias());

        $filter = $query->getFilter();
        $filter = $this->searchTextFilterReducer->reduceToFilterCondition($filter);
        $query->setFilter($filter);

        $select = $this->selectGenerator->generate($query);
        $event = new GenerateSelectsEvent($select, $this->getFromAlias());
        $this->dataSource->getEventDispatcher()->dispatch(DoctrineDriver::EVENT_GENERATE_SELECTS, $event);
        $select = $event->select;
        $queryBuilder->add('select', $select);

        $joins = $this->joinGenerator->generate($query);
        $event = new GenerateJoinsEvent($this->getFromAlias(), $joins);
        $this->dataSource->getEventDispatcher()->dispatch(DoctrineDriver::EVENT_GENERATE_JOINS, $event);
        $joins = $event->joins;
        foreach ($joins as $join) {
            $queryBuilder
                ->leftJoin($join->getJoin(), $join->getAlias(), $join->getConditionType(), $join->getCondition(), $join->getIndexBy());
        }

        $this->filterer->filter($queryBuilder, $query->getFilter(), $this->selectGenerator->getUniqueNameToSelectFieldMap($query));

        $this->sorter->sort($queryBuilder, $query->getSort(), $this->selectGenerator->getUniqueNameToSelectFieldMap($query));

        $this->paginator->paginate($queryBuilder, $query->getPagination(), $this->dataSource->getFields());

        $this->dataSource->getEventDispatcher()->dispatch(DoctrineDriver::EVENT_POST_GENERATE_QUERY_BUILDER, new PostGenerateQueryBuilderEvent($queryBuilder, $this->getFromAlias()));

        return $queryBuilder;
    }

    /**
     * Gets the FROM alias, an internal name given to the class in the FROM part of the DQL.
     *
     * This name is generated once, and it's unique per execution of the data source.
     *
     * @return string
     */
    protected function getFromAlias()
    {
        if (is_null($this->fromAlias)) {
            $this->fromAlias = uniqid('ENTITY_');
        }

        return $this->fromAlias;
    }
}
