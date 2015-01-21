<?php

namespace Netdudes\DataSourceryBundle\Query;

class Query implements QueryInterface
{
    protected $filter;

    protected $sort;

    protected $pagination;

    protected $select = [];

    public function __construct()
    {
        $this->sort = new Sort();
        $this->filter = new Filter();
        $this->filter->setConditionType(Filter::CONDITION_TYPE_AND);
        $this->pagination = new Pagination();
    }

    /**
     * @return Filter
     */
    public function getFilter()
    {
        return $this->filter;
    }

    public function setFilter(Filter $filter)
    {
        $this->filter = $filter;
    }

    /**
     * @return Pagination
     */
    public function getPagination()
    {
        return $this->pagination;
    }

    public function setPagination(Pagination $pagination)
    {
        $this->pagination = $pagination;
    }

    /**
     * @return array
     */
    public function getSelect()
    {
        return $this->select;
    }

    /**
     * @param array $elements
     */
    public function setSelect(array $elements)
    {
        $this->select = $elements;
    }

    /**
     * @return Sort
     */
    public function getSort()
    {
        return $this->sort;
    }

    /**
     * @param Sort $sort
     */
    public function setSort(Sort $sort)
    {
        $this->sort = $sort;
    }

    /**
     * @param SortCondition $sortCondition
     */
    public function addSortCondition(SortCondition $sortCondition)
    {
        if (is_null($this->sort)) {
            $this->sort = new Sort();
        }
        $this->sort[] = $sortCondition;
    }

    /**
     * Figure out a relation of all required fields to be selected, including the fields
     * needed for the selected columns, the fields required from the set filters, and
     * any field set to be sorted by.
     *
     * @return array
     */
    public function extractRequiredFields()
    {
        $requiredFields = [];

        // Extract required fields from the select
        if (!is_null($this->select)) {
            foreach ($this->getSelect() as $selectedField) {
                $requiredFields[] = $selectedField;
            }
        }

        // Flatten and extract all filtered fields
        $flatFilterConditions = $this->getFilter()->getAllFilterConditionsFlat();
        foreach ($flatFilterConditions as $filterCondition) {
            $requiredFields[] = $filterCondition->getFieldName();
        }

        // Extract all sort fields
        /** @var $sortCondition SortCondition */
        foreach ($this->getSort() as $sortCondition) {
            $requiredFields[] = $sortCondition->getFieldName();
        }

        return array_unique($requiredFields);
    }
}
