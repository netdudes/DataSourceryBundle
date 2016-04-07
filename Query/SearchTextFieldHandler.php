<?php
namespace Netdudes\DataSourceryBundle\Query;

use Netdudes\DataSourceryBundle\DataSource\Configuration\Field;
use Netdudes\DataSourceryBundle\DataType\SearchTextDataType;

class SearchTextFieldHandler
{
    /**
     * @var SearchTextFilterConditionTransformer
     */
    private $subTransformer;

    /**
     * @param SearchTextFilterConditionTransformer $subTransformer
     */
    public function __construct(SearchTextFilterConditionTransformer $subTransformer)
    {
        $this->subTransformer = $subTransformer;
    }

    /**
     * @param Filter  $filter
     * @param Field[] $dataSourceFields
     *
     * @return Filter
     */
    public function handle(Filter $filter, array $dataSourceFields)
    {
        foreach ($filter as $index => $subFilterOrFilterCondition) {
            if ($subFilterOrFilterCondition instanceof Filter) {
                $this->handle($subFilterOrFilterCondition, $dataSourceFields);
                continue;
            }

            if ($subFilterOrFilterCondition instanceof FilterCondition) {
                $this->handleFilterCondition($filter, $index, $subFilterOrFilterCondition, $dataSourceFields);
            }
        }
    }

    /**
     * @param Filter          $filter
     * @param int             $index
     * @param FilterCondition $filterCondition
     * @param Field[]         $dataSourceFields
     *
     * @throws \Exception
     */
    private function handleFilterCondition(Filter $filter, $index, FilterCondition $filterCondition, array $dataSourceFields)
    {
        $fieldName = $filterCondition->getFieldName();
        $field = $this->findFieldByName($dataSourceFields, $fieldName);

        if ($field->getDataType() instanceof SearchTextDataType) {
            $filter[$index] = $this->subTransformer->transform($filterCondition, $dataSourceFields);
        }
    }

    /**
     * @param Field[] $dataSourceFields
     * @param string  $fieldName
     *
     * @return Field
     *
     * @throws \Exception
     */
    private function findFieldByName(array $dataSourceFields, $fieldName)
    {
        foreach ($dataSourceFields as $field) {
            if ($field->getUniqueName() === $fieldName) {
                return $field;
            }
        }

        throw new \Exception("The $fieldName was not found in data source");
    }
}
