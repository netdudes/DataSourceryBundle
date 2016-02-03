<?php

namespace Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\QueryBuilder;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Composite;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
use Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\Exception\ColumnNotFoundException;
use Netdudes\DataSourceryBundle\Query\Filter;
use Netdudes\DataSourceryBundle\Query\FilterCondition;

class Filterer
{
    /**
     * @var array
     */
    private $uniqueNameToQueryFieldMap;

    /**
     * Apply the filters to the query builders
     *
     * @param QueryBuilder $queryBuilder
     * @param Filter       $filterDefinition
     *
     * @param              $selectFieldsMap
     * @param              $fields
     *
     * @return QueryBuilder
     * @throws \Exception
     */
    public function filter(QueryBuilder $queryBuilder, Filter $filterDefinition, $uniqueNameToQueryFieldMap)
    {
        $this->uniqueNameToQueryFieldMap = $uniqueNameToQueryFieldMap;
        $expressions = $this->buildFilterGroup($queryBuilder, $filterDefinition);
        if ($expressions->count() > 0) {
            $queryBuilder->andWhere($expressions);
        }

        return $queryBuilder;
    }

    /**
     * Builds a filter group and, recursively, constructs a tree of conditions for the filters
     *
     * @param QueryBuilder $queryBuilder
     * @param Filter       $filterDefinition
     *
     * @throws \Exception
     * @return Andx|Orx
     */
    protected function buildFilterGroup(QueryBuilder $queryBuilder, Filter $filterDefinition)
    {
        // Container for the expressions to add to the $queryBuilder
        $filterConditionType = $filterDefinition->getConditionType();
        if ($filterConditionType == Filter::CONDITION_TYPE_AND) {
            $expressions = $queryBuilder->expr()->andX();
        } elseif ($filterConditionType == Filter::CONDITION_TYPE_OR) {
            $expressions = $queryBuilder->expr()->orX();
        } else {
            throw new \Exception("Unknown condition type $filterConditionType on the filter.");
        }

        // Loop through all the filters in the collection
        $this->addExpressionsForFilter($expressions, $filterDefinition, $queryBuilder);

        return $expressions;
    }

    /**
     * @param Composite    $expressions
     *
     * @param Filter       $filterDefinition
     * @param QueryBuilder $queryBuilder
     *
     * @throws \Exception
     */
    protected function addExpressionsForFilter(Composite $expressions, Filter $filterDefinition, QueryBuilder $queryBuilder)
    {
        foreach ($filterDefinition as $subFilterDefinition) {
            if ($subFilterDefinition instanceof Filter) {
                // If the element is itself a Collection, recursively build it
                $expressions->add($this->buildFilterGroup($queryBuilder, $subFilterDefinition));
            } elseif ($subFilterDefinition instanceof FilterCondition) {
                $this->addExpressionsForFilterCondition($expressions, $subFilterDefinition, $queryBuilder);
            }
        }
    }

    /**
     * @param Composite    $expressions
     * @param              $filterCondition
     * @param QueryBuilder $queryBuilder
     *
     * @throws \Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\Exception\ColumnNotFoundException
     * @throws \Exception
     */
    protected function addExpressionsForFilterCondition(Composite $expressions, FilterCondition $filterCondition, QueryBuilder $queryBuilder)
    {
        // Build an unique token name for parameter substitution
        $token = $this->buildUniqueToken($filterCondition, $queryBuilder);
        $filterMethod = $filterCondition->getMethod();

        // Add the filtering statement
        $valueInDatabase = $filterCondition->getValueInDatabase();

        // Flag to not insert the parameter if the logic requires it
        $ignoreParameter = false;

        // Depending on the filter type, create a condition
        $condition = $this->buildCondition($filterCondition, $token, $queryBuilder);
        $expressions->add($condition);

        // Ignore value if needed
        if (
            ($filterMethod == FilterCondition::METHOD_IN && count($valueInDatabase) <= 0) ||
            ($filterMethod == FilterCondition::METHOD_IS_NULL)
        ) {
            $ignoreParameter = true;
        }

        // Modify the value if needed
        if ($filterMethod == FilterCondition::METHOD_STRING_LIKE) {
            $valueInDatabase = str_replace('*', '%', $valueInDatabase);
        }

        // Insert the value substituting the token
        if (!$ignoreParameter) {
            $queryBuilder->setParameter($token, $valueInDatabase);
        }
    }

    /**
     * @param FilterCondition $filterCondition
     * @param                 $token
     * @param QueryBuilder    $queryBuilder
     *
     * @throws \Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\Exception\ColumnNotFoundException
     * @throws \Exception
     *
     * @return Expr|string
     */
    protected function buildCondition(FilterCondition $filterCondition, $token, QueryBuilder $queryBuilder)
    {
        $filterMethod = $filterCondition->getMethod();
        $identifier = $this->uniqueNameToQueryFieldMap[$filterCondition->getFieldName()];
        $value = $filterCondition->getValue();

        if ($filterMethod == FilterCondition::METHOD_STRING_LIKE) {
            return $queryBuilder->expr()->like($identifier, $token);
        } elseif ($filterMethod == FilterCondition::METHOD_STRING_EQ ||
            $filterMethod == FilterCondition::METHOD_NUMERIC_EQ ||
            $filterMethod == FilterCondition::METHOD_BOOLEAN ||
            $filterMethod == FilterCondition::METHOD_DATETIME_EQ
        ) {
            return $queryBuilder->expr()->eq($identifier, $token);
        } elseif ($filterMethod == FilterCondition::METHOD_NUMERIC_GT || $filterMethod == FilterCondition::METHOD_DATETIME_GT) {
            return $queryBuilder->expr()->gt($identifier, $token);
        } elseif ($filterMethod == FilterCondition::METHOD_NUMERIC_GTE || $filterMethod == FilterCondition::METHOD_DATETIME_GTE) {
            return $queryBuilder->expr()->gte($identifier, $token);
        } elseif ($filterMethod == FilterCondition::METHOD_NUMERIC_EQ || $filterMethod == FilterCondition::METHOD_DATETIME_EQ) {
            return $queryBuilder->expr()->eq($identifier, $token);
        } elseif ($filterMethod == FilterCondition::METHOD_NUMERIC_LTE || $filterMethod == FilterCondition::METHOD_DATETIME_LTE) {
            return $queryBuilder->expr()->lte($identifier, $token);
        } elseif ($filterMethod == FilterCondition::METHOD_NUMERIC_LT || $filterMethod == FilterCondition::METHOD_DATETIME_LT) {
            return $queryBuilder->expr()->lt($identifier, $token);
        } elseif ($filterMethod == FilterCondition::METHOD_STRING_NEQ || $filterMethod == FilterCondition::METHOD_NUMERIC_NEQ || $filterMethod == FilterCondition::METHOD_DATETIME_NEQ) {
            return $queryBuilder->expr()->neq($identifier, $token);
        } elseif ($filterMethod == FilterCondition::METHOD_IN) {
            if (!is_array($value)) {
                throw new \Exception('Only arrays can be arguments of a METHOD_IN filter');
            }

            if (count($filterCondition->getValue()) > 0) {
                return $queryBuilder->expr()->in($identifier, $token);
            }
            // The array is empty, therefore this will always be "false". We use an always-false expression
            // to emulate this without actually using an invalid empty array in the IN statement.
            return '1=2';
        } elseif ($filterMethod == FilterCondition::METHOD_IS_NULL) {
            if ($filterCondition->getValue()) {
                return $queryBuilder->expr()->isNull($identifier);
            }

            return $queryBuilder->expr()->isNotNull($identifier);
        } else {
            throw new \Exception("Unknown filtering method \"$filterMethod\" for column \"" . $filterCondition->getFieldName() . '"');
        }
    }

    /**
     * @param              $filterCondition
     * @param QueryBuilder $queryBuilder
     *
     * @return string
     * @throws \Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\Exception\ColumnNotFoundException
     */
    protected function buildUniqueToken(FilterCondition $filterCondition, QueryBuilder $queryBuilder)
    {
        return ':token_'
        . strtolower(str_replace(['.', '-'], '_', $filterCondition->getFieldName()))
        . '_' . $queryBuilder->getParameters()->count();
    }

    /**
     * Helper method: transforms a column identifier to a database field for use
     * in filtering and sorting
     *
     * @param   $dataSourceFieldUniqueName
     *
     * @throws \Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\Exception\ColumnNotFoundException
     * @return mixed
     */
    protected function getDatabaseFilterQueryFieldByDataSourceFieldUniqueName($fields, $dataSourceFieldUniqueName)
    {
        /** @var $dataSourceField \Netdudes\DataSourceryBundle\DataSource\Configuration\Field */
        $dataSourceField = null;
        foreach ($fields as $field) {
            if ($field->getUniqueName() == $dataSourceFieldUniqueName) {
                $dataSourceField = $field;
                break;
            }
        }

        if (is_null($dataSourceField)) {
            throw new ColumnNotFoundException("Could not find column \"$dataSourceFieldUniqueName\" in the data source");
        }

        return $dataSourceField->getDatabaseFilterQueryField();
    }
}
