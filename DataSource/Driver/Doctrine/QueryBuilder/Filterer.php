<?php

namespace Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\QueryBuilder;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Andx;
use Doctrine\ORM\Query\Expr\Composite;
use Doctrine\ORM\Query\Expr\Orx;
use Doctrine\ORM\QueryBuilder;
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
     * @param array        $uniqueNameToQueryFieldMap
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
    private function buildFilterGroup(QueryBuilder $queryBuilder, Filter $filterDefinition)
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
     * @param Filter       $filterDefinition
     * @param QueryBuilder $queryBuilder
     *
     * @throws \Exception
     */
    private function addExpressionsForFilter(Composite $expressions, Filter $filterDefinition, QueryBuilder $queryBuilder)
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
     * @param Composite       $expressions
     * @param FilterCondition $filterCondition
     * @param QueryBuilder    $queryBuilder
     *
     * @throws \Exception
     */
    private function addExpressionsForFilterCondition(Composite $expressions, FilterCondition $filterCondition, QueryBuilder $queryBuilder)
    {
        $token = $this->buildUniqueToken($filterCondition, $queryBuilder);

        $expression = $this->buildExpression($filterCondition, $token, $queryBuilder);
        $expressions->add($expression);

        $this->setExpressionParameters($filterCondition, $token, $queryBuilder);
    }

    /**
     * @param FilterCondition $filterCondition
     * @param QueryBuilder    $queryBuilder
     *
     * @return string
     */
    private function buildUniqueToken(FilterCondition $filterCondition, QueryBuilder $queryBuilder)
    {
        return ':token_'
            . strtolower(str_replace(['.', '-'], '_', $filterCondition->getFieldName()))
            . '_' . $queryBuilder->getParameters()->count();
    }

    /**
     * @param FilterCondition $filterCondition
     * @param string          $token
     * @param QueryBuilder    $queryBuilder
     *
     * @throws \Exception
     *
     * @return Expr|string
     */
    private function buildExpression(FilterCondition $filterCondition, $token, QueryBuilder $queryBuilder)
    {
        $identifier = $this->uniqueNameToQueryFieldMap[$filterCondition->getFieldName()];

        if (null === $filterCondition->getValue()) {
            return $this->buildExpressionForNullValue($filterCondition, $identifier, $queryBuilder);
        }

        $filterMethod = $filterCondition->getMethod();
        switch ($filterMethod) {
            case FilterCondition::METHOD_STRING_LIKE:
                return $queryBuilder->expr()->like($identifier, $token);
            case FilterCondition::METHOD_STRING_EQ:
            case FilterCondition::METHOD_NUMERIC_EQ:
            case FilterCondition::METHOD_BOOLEAN:
            case FilterCondition::METHOD_DATETIME_EQ:
                return $queryBuilder->expr()->eq($identifier, $token);
            case FilterCondition::METHOD_NUMERIC_GT:
            case FilterCondition::METHOD_DATETIME_GT:
                return $queryBuilder->expr()->gt($identifier, $token);
            case FilterCondition::METHOD_NUMERIC_GTE:
            case FilterCondition::METHOD_DATETIME_GTE:
                return $queryBuilder->expr()->gte($identifier, $token);
            case FilterCondition::METHOD_NUMERIC_LTE:
            case FilterCondition::METHOD_DATETIME_LTE:
                return $queryBuilder->expr()->lte($identifier, $token);
            case FilterCondition::METHOD_NUMERIC_LT:
            case FilterCondition::METHOD_DATETIME_LT:
                return $queryBuilder->expr()->lt($identifier, $token);
            case FilterCondition::METHOD_STRING_NEQ:
            case FilterCondition::METHOD_NUMERIC_NEQ:
            case FilterCondition::METHOD_DATETIME_NEQ:
                return $queryBuilder->expr()->neq($identifier, $token);
            case FilterCondition::METHOD_IN:
            case FilterCondition::METHOD_NIN:
                return $this->buildExpressionForCollection($filterCondition, $identifier, $token, $queryBuilder);
            default:
                throw new \Exception("Unknown filtering method \"$filterMethod\" for column \"" . $filterCondition->getFieldName() . '"');
        }
    }

    /**
     * @param FilterCondition $filterCondition
     * @param string          $identifier
     * @param QueryBuilder    $queryBuilder
     *
     * @return Andx|Orx
     *
     * @throws \Exception
     */
    private function buildExpressionForNullValue(FilterCondition $filterCondition, $identifier, QueryBuilder $queryBuilder)
    {
        $method = $filterCondition->getMethod();

        $isEmptyFiltering = in_array($method, [
            FilterCondition::METHOD_STRING_EQ,
            FilterCondition::METHOD_NUMERIC_EQ,
            FilterCondition::METHOD_DATETIME_EQ,
        ]);
        if ($isEmptyFiltering) {
            return $queryBuilder->expr()->orX(
                $queryBuilder->expr()->eq($identifier, $queryBuilder->expr()->literal('')),
                $queryBuilder->expr()->isNull($identifier)
            );
        }

        $isNotEmptyFiltering = in_array($method, [
            FilterCondition::METHOD_STRING_NEQ,
            FilterCondition::METHOD_NUMERIC_NEQ,
            FilterCondition::METHOD_DATETIME_NEQ,
        ]);
        if ($isNotEmptyFiltering) {
            return $queryBuilder->expr()->andX(
                $queryBuilder->expr()->neq($identifier, $queryBuilder->expr()->literal('')),
                $queryBuilder->expr()->isNotNull($identifier)
            );
        }

        throw new \Exception("The $method operator cannot be used to compare against null value");
    }

    /**
     * @param FilterCondition $filterCondition
     * @param string          $identifier
     * @param string          $token
     * @param QueryBuilder    $queryBuilder
     *
     * @return Andx|Orx|string
     *
     * @throws \Exception
     */
    private function buildExpressionForCollection(FilterCondition $filterCondition, $identifier, $token, QueryBuilder $queryBuilder)
    {
        $method = $filterCondition->getMethod();
        $value = $filterCondition->getValue();

        if (!is_array($value)) {
            throw new \Exception('Only arrays can be arguments of a METHOD_IN or METHOD_NIN filter');
        }

        if (count($value) <= 0) {
            // The array is empty, therefore this will always be "false". We use an always-false expression
            // to emulate this without actually using an invalid empty array in the IN statement.
            return '1=2';
        }

        if ($method === FilterCondition::METHOD_IN) {
            return $queryBuilder->expr()->in($identifier, $token);
        } else {
            return $queryBuilder->expr()->notIn($identifier, $token);
        }
    }

    /**
     * @param FilterCondition $filterCondition
     * @param string          $token
     * @param QueryBuilder    $queryBuilder
     */
    private function setExpressionParameters(FilterCondition $filterCondition, $token, QueryBuilder $queryBuilder)
    {
        $filterMethod = $filterCondition->getMethod();

        $filteringUsingInOperator = in_array($filterMethod, [FilterCondition::METHOD_IN, FilterCondition::METHOD_NIN]);
        if ($filteringUsingInOperator && count($filterCondition->getValue()) <= 0) {
            return;
        }

        $comparingAgainstNull = null === $filterCondition->getValue();
        if ($comparingAgainstNull) {
            return;
        }

        $valueInDatabase = $filterCondition->getValueInDatabase();
        if ($filterMethod == FilterCondition::METHOD_STRING_LIKE) {
            $valueInDatabase = str_replace('*', '%', $valueInDatabase);
        }

        $queryBuilder->setParameter($token, $valueInDatabase);
    }
}
