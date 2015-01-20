<?php
namespace Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\QueryBuilder;

use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\EntityManager;
use Netdudes\DataSourceryBundle\DataSource\DataSourceInterface;
use Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\DoctrineDriver;
use Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\Events\GenerateJoinsEvent;
use Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\Events\GenerateSelectsEvent;
use Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\Events\PostGenerateQueryBuilderEvent;
use Netdudes\DataSourceryBundle\Query\Query;

class Builder
{
    protected $filterer;

    protected $sorter;

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
     * @var \Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\QueryBuilder\RequiredFieldsExtractor
     */
    protected $requiredFieldsExtractor;

    /**
     * @var \Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\QueryBuilder\JoinGenerator
     */
    protected $joinGenerator;

    /**
     * @var \Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\QueryBuilder\SelectGenerator
     */
    protected $selectGenerator;

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
     * @param EntityManager             $entityManager
     */
    public function __construct(DataSourceInterface $dataSource, EntityManager $entityManager)
    {
        $fields = $dataSource->getFields();
        $transformers = $dataSource->getTransformers();
        $this->entityManager = $entityManager;
        // TODO: Fully dependency inject through some pattern (factory of factories ?) that hides the dependencies from the end-developer's DataSource
        $this->requiredFieldsExtractor = new RequiredFieldsExtractor($fields, $transformers);
        $this->joinGenerator = new JoinGenerator($fields, $this->getFromAlias(), $this->requiredFieldsExtractor);
        $this->selectGenerator = new SelectGenerator($fields, $this->getFromAlias(), $this->joinGenerator, $this->requiredFieldsExtractor);
        $this->filterer = new Filterer();
        $this->sorter = new Sorter();
        $this->paginator = new Paginator();
        $this->dataSource = $dataSource;
    }

    /**
     * Gets the fully generated query builder. Will autogenerate select and
     * join statements as needed.
     *
     * This function is cached, and will only be generated once per execution.
     *
     * @param Query $query
     *
     * @return QueryBuilder|null
     */
    public function buildQueryBuilder(Query $query, $entityClass)
    {
        $queryBuilder = $this->entityManager->createQueryBuilder();

        $queryBuilder->from($entityClass, $this->getFromAlias());

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
            $this->fromAlias = uniqid("ENTITY_");
        }

        return $this->fromAlias;
    }
}
