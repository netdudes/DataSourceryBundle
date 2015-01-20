<?php
namespace Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine;

use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Netdudes\DataSourceryBundle\DataSource\Configuration\Field;
use Netdudes\DataSourceryBundle\DataSource\DataSourceInterface;
use Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\Events\PostFetchEvent;
use Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\Exception\ColumnNotFoundException;
use Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\Exception\ColumnNotSelectedException;
use Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\QueryBuilder\BuilderFactory;
use Netdudes\DataSourceryBundle\DataSource\Driver\DriverInterface;
use Netdudes\DataSourceryBundle\Query\Query;
use Netdudes\DataSourceryBundle\Query\QueryInterface;

class DoctrineDriver implements DriverInterface
{
    const EVENT_GENERATE_SELECTS = 1;
    const EVENT_GENERATE_JOINS = 2;
    const EVENT_POST_GENERATE_QUERY_BUILDER = 3;
    const EVENT_POST_FETCH = 4;

    /**
     * @var BuilderFactory
     */
    private $queryBuilderBuilderFactory;

    /**
     * @param BuilderFactory $queryBuilderBuilderFactory
     */
    public function __construct(BuilderFactory $queryBuilderBuilderFactory)
    {
        $this->queryBuilderBuilderFactory = $queryBuilderBuilderFactory;
    }

    /**
     * {@inheritdoc}
     */
    public function getData(DataSourceInterface $dataSource, QueryInterface $query)
    {
        $queryBuilder = $this->getQueryBuilder($dataSource, $query);
        $rows = $this->fetchData($queryBuilder, $query, $dataSource);

        return $rows;
    }

    /**
     * {@inheritdoc}
     */
    public function getRecordCount(DataSourceInterface $dataSource, QueryInterface $query)
    {
        $queryBuilder = $this->getQueryBuilder($dataSource, $query);
        $queryBuilder->select('count(DISTINCT ' . $queryBuilder->getDQLPart('from')[0]->getAlias() . ')');
        $queryBuilder->resetDQLPart('groupBy');
        $queryBuilder->resetDQLPart('orderBy');
        try {
            return $queryBuilder->getQuery()->getSingleScalarResult();
        } catch (NoResultException $e) {
            return 0;
        }
    }

    /**
     * @param DataSourceInterface $dataSource
     * @param QueryInterface      $query
     *
     * @return \Doctrine\DBAL\Query\QueryBuilder|null
     */
    public function getQueryBuilder(DataSourceInterface $dataSource, QueryInterface $query)
    {
        $queryBuilderBuilder = $this->queryBuilderBuilderFactory->create($dataSource);
        $queryBuilder = $queryBuilderBuilder->buildQueryBuilder($query, $dataSource->getEntityClass());

        return $queryBuilder;
    }

    /**
     * Transforms a fully-built query builder into a row collection with the results
     *
     * @param QueryBuilder        $queryBuilder
     *
     * @param Query               $query
     * @param DataSourceInterface $dataSource
     *
     * @return array
     * @throws ColumnNotSelectedException
     * @throws \Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\Exception\ColumnNotFoundException
     *
     */
    protected function fetchData(QueryBuilder $queryBuilder, Query $query, DataSourceInterface $dataSource)
    {
        $fields = $dataSource->getFields();
        $rowCollection = [];
        $queryResults = $queryBuilder->getQuery()->getResult();
        foreach ($queryResults as $queryResultsRow) {
            $row = [];
            foreach ($fields as $queryBuilderDataSourceField) {
                if (!in_array($queryBuilderDataSourceField->getUniqueName(), $query->getSelect(), true)) {
                    continue;
                }

                $row[$queryBuilderDataSourceField->getUniqueName()] =
                    $this->getCellValueByDataSourceField($queryResultsRow, $queryBuilderDataSourceField, $fields);
            }
            $rowCollection[] = $row;
        }

        $event = new PostFetchEvent($dataSource, $rowCollection);
        $dataSource->getEventDispatcher()->dispatch(
            self::EVENT_POST_FETCH,
            $event
        );

        return $event->data;
    }

    /**
     * Helper method: gets the data corresponding to a given table column
     * from a row of the database scalar results
     *
     * @param array                                                    $dataRow Plain array of data for a single row, from the database
     * @param \Netdudes\DataSourceryBundle\DataSource\Configuration\Field $dataSourceField
     *
     * @throws \Exception
     *
     * @return mixed
     */
    protected function getCellValueByDataSourceField(array $dataRow, Field $dataSourceField, $fields)
    {
        $selectAlias = $dataSourceField->getDatabaseSelectAlias();

        if (is_array($selectAlias)) {
            // Alias is an array of aliases. The cell value is an array then too.
            $cellValue = [];
            foreach ($selectAlias as $key => $subAlias) {
                // Resolve recursively
                $field = $this->findQueryBuilderDataSourceFieldByUniqueName($subAlias, $fields);
                if (is_null($field)) {
                    throw new ColumnNotFoundException("Could not find data source field $subAlias, alias of  $selectAlias");
                }
                foreach ($fields as $field) {
                    if ($field->getUniqueName() === $subAlias) {
                        $cellValue[$key] = $this->getCellValueByDataSourceField($dataRow, $field, $fields);
                        break;
                    }
                }
            }

            return $cellValue;
        }

        // Try to get the data from the result row
        if (array_key_exists($selectAlias, $dataRow)) {
            return $dataRow[$selectAlias];
        }

        throw new ColumnNotSelectedException("Value for column '$selectAlias' cannot be found as the field was not selected");
    }

    private function findQueryBuilderDataSourceFieldByUniqueName($uniqueName, $fields)
    {
        foreach ($fields as $field) {
            if ($field->getUniqueName() === $uniqueName) {
                return $field;
            }
        }

        return null;
    }
}
