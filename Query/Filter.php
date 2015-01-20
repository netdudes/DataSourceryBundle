<?php

namespace Netdudes\DataSourceryBundle\Query;

use Netdudes\DataSourceryBundle\DataSource\Configuration\Field;
use Netdudes\DataSourceryBundle\DataSource\DataSourceInterface;
use Netdudes\DataSourceryBundle\Util\AbstractArrayAccessibleCollection;

/**
 * Group of filters bundled with a condition type
 */
class Filter extends AbstractArrayAccessibleCollection implements \JsonSerializable
{
    /**
     * The filters are concatenated AND-wise
     */
    const CONDITION_TYPE_AND = 'AND';

    /**
     * The filters are concatenated OR-wise
     */
    const CONDITION_TYPE_OR = 'OR';

    /**
     * The filters are concatenated XOR-wise
     * Note: This is not currently supported by Doctrine, but left in for completeness
     */
    const CONDITION_TYPE_XOR = 'XOR';

    /**
     * Condition concatenation type, by default OR
     *
     * @var int
     */
    private $conditionType = self::CONDITION_TYPE_AND;

    /**
     * Helper method:
     * Build a filter collection from a full-text search term and a column definition collection
     *
     * @param DataSourceInterface $dataSource
     * @param string              $searchTerm
     *
     * @deprecated The generation of complex filters should not be a concern of the filter itself. This method should be avoided and phased out.
     *
     * TODO: Remove this method and replace it with an explicit full-text search functionality or an external mechanism to generate this filters.
     *
     * @return array|Filter
     */
    public static function createFromFullTextSearch(DataSourceInterface $dataSource, $searchTerm)
    {
        // Build a filter collection
        $filterDefinition = new self();
        $searchTerm = "%$searchTerm%";

        // Fill in with filters for every searchable column
        $dataSourceElements = $dataSource->getFields();
        foreach ($dataSourceElements as $dataSourceElement) {
            $dataSourceElementSupportsLike = $dataSourceElement->getDataType()->supports(FilterCondition::METHOD_STRING_LIKE);
            // TODO: This is necessary to prevent elements from a QueryBuilderDataSource being used in full text search when they don't point to any field.
            // THIS IS A HACK. Should be solved when this whole method is removed.
            $dataSourceElementIsEntityField =
                $dataSourceElement instanceof Field ?
                    (bool) ($dataSourceElement->getDatabaseFilterQueryField()) :
                    true;
            if ($dataSourceElementSupportsLike && $dataSourceElementIsEntityField) {
                $filter = new FilterCondition($dataSourceElement->getUniqueName(), FilterCondition::METHOD_STRING_LIKE, $searchTerm);
                $filterDefinition[] = $filter;
            }
        }

        // Set the filter collection to OR
        $filterDefinition->setConditionType(Filter::CONDITION_TYPE_OR);

        return $filterDefinition;
    }

    /**
     * Create a FilterDefinition from a deserialized compact exchange JSON filters.
     * This wil recursively call itself to unpack the whole filter tree.
     *
     * @param $jsonSerializable
     *
     * @return array|Filter|FilterCondition
     * @throws \Exception
     */
    public static function createFromJsonSerializable($jsonSerializable)
    {
        if (!count((array) $jsonSerializable)) {
            // Edge case: empty filter
            return new Filter();
        }

        if (count($jsonSerializable) == 2 && is_array($jsonSerializable[1])) {
            // Complex Filter
            $logic = $jsonSerializable[0];
            $elements = $jsonSerializable[1];
            $filter = new Filter();
            $filter->setConditionType($logic);
            foreach ($elements as $element) {
                $filter[] = static::createFromJsonSerializable($element);
            }

            return $filter;
        } elseif (count($jsonSerializable) == 3) {
            // Simple element
            return new FilterCondition($jsonSerializable[0], $jsonSerializable[1], $jsonSerializable[2]);
        }

        throw new \Exception('Unable to parse JSON-serializable filter structure');
    }

    public function toUQL()
    {
        $parts = [];
        foreach ($this as $filter) {
            $subFilterUql = $filter->toUQL();
            if ($filter instanceof Filter) {
                $subFilterUql = '(' . $subFilterUql . ')';
            }
            $parts[] = $subFilterUql;
        }

        return implode(" " . $this->conditionType . " ", $parts);
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     *               which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        return $this->toJsonSerializable();
    }

    /**
     * Recursively compact into a easily JSON-serializable array
     * representation
     */
    public function toJsonSerializable()
    {
        $serializableArray = [
            'logic' => $this->getConditionType(),
            'elements' => [],
        ];
        foreach ($this as $filter) {
            $serializableArray['elements'][] = $filter->toJsonSerializable();
        }

        return $serializableArray;
    }

    /**
     * @return int
     */
    public function getConditionType()
    {
        return $this->conditionType;
    }

    /**
     * @param int $conditionType
     */
    public function setConditionType($conditionType)
    {
        $this->conditionType = $conditionType;
    }

    public function getAllFirstLevelFilteredDataSourceUniqueNames()
    {
        $filteredDataSourceIds = [];
        foreach ($this as $element) {
            if ($element instanceof FilterCondition) {
                $filteredDataSourceIds[] = $element->getFieldName();
            }
        }

        return $filteredDataSourceIds;
    }

    /**
     * Flatten the tree of Filters into a one-dimensional array
     * or FilterConditions.
     *
     * @return FilterCondition[]
     */
    public function getAllFilterConditionsFlat()
    {
        $flatConditionsList = [];
        $flatten = function (Filter $filterDefinition) use (&$flatConditionsList, &$flatten) {
            foreach ($filterDefinition as $filter) {
                if ($filter instanceof Filter) {
                    $flatten($filter);
                } elseif ($filter instanceof FilterCondition) {
                    $flatConditionsList[] = $filter;
                }
            }
        };
        $flatten($this);

        return $flatConditionsList;
    }
}
