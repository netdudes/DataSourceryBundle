<?php
namespace Netdudes\DataSourceryBundle\Query;

use Netdudes\DataSourceryBundle\DataSource\Configuration\Field;

class SearchTextFilterReducer
{
    /**
     * @var Field[]
     */
    private $dataSourceFields;

    /**
     * @param Field[] $dataSourceFields
     */
    public function __construct(array $dataSourceFields)
    {
        $this->dataSourceFields = $dataSourceFields;
    }

    /**
     * @param Filter $filterToReduce
     *
     * @return Filter
     */
    public function reduceToFilterCondition(Filter $filterToReduce)
    {
        $reducedFilter = new Filter();
        $reducedFilter->setConditionType($filterToReduce->getConditionType());
        foreach ($filterToReduce as $filterOrFilterCondition) {
            if ($filterOrFilterCondition instanceof Filter) {
                $reducedFilter[] = $this->reduceToFilterCondition($filterOrFilterCondition);
            } elseif ($filterOrFilterCondition instanceof FilterCondition) {
                if ($filterOrFilterCondition->isSearchText()) {
                    $filterOrFilterCondition = $this->reduceSearchTextFilterCondition($filterOrFilterCondition);
                }
                $reducedFilter[] = $filterOrFilterCondition;
            }
        }

        return $reducedFilter;
    }

    /**
     * @param FilterCondition $filterCondition
     *
     * @return Filter
     */
    private function reduceSearchTextFilterCondition(FilterCondition $filterCondition)
    {
        $method = $filterCondition->getMethod();
        $searchTerm = $filterCondition->getValue();
        if ($method === FilterCondition::METHOD_STRING_LIKE) {
            $searchTerm = "%$searchTerm%";
        }

        $filter = new Filter();
        $filter->setConditionType(Filter::CONDITION_TYPE_OR);
        foreach ($this->dataSourceFields as $dataSourceField) {
            if (!$dataSourceField->getDataType()->supports($method)) {
                continue;
            }
            if (!$dataSourceField->getDatabaseFilterQueryField()) {
                continue;
            }
            if (is_array($dataSourceField->getDatabaseSelectAlias())) {
                continue;
            }

            $filter[] = new FilterCondition($dataSourceField->getUniqueName(), $method, $searchTerm, $searchTerm);
        }

        return $filter;
    }
}
