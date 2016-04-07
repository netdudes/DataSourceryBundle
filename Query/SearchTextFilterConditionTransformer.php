<?php
namespace Netdudes\DataSourceryBundle\Query;

use Netdudes\DataSourceryBundle\DataSource\Configuration\Field;

class SearchTextFilterConditionTransformer
{
    /**
     * @param FilterCondition $filterCondition
     * @param Field[]         $dataSourceFields
     *
     * @return Filter
     */
    public function transform(FilterCondition $filterCondition, array $dataSourceFields)
    {
        $filter = new Filter();
        $filter->setConditionType(Filter::CONDITION_TYPE_OR);

        if (empty($dataSourceFields)) {
            return $filter;
        }

        $method = $filterCondition->getMethod();
        $searchTerm = $filterCondition->getValue();
        if ($method === FilterCondition::METHOD_STRING_LIKE) {
            $searchTerm = "%$searchTerm%";
        }

        foreach ($dataSourceFields as $dataSourceField) {
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
