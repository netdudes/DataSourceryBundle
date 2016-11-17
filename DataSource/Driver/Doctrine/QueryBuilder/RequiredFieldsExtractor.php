<?php
namespace Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\QueryBuilder;

use Netdudes\DataSourceryBundle\DataSource\Configuration\Field;
use Netdudes\DataSourceryBundle\Query\Query;
use Netdudes\DataSourceryBundle\Transformers\TransformerInterface;

class RequiredFieldsExtractor
{
    /**
     * @var array[][]
     */
    protected $fieldsCache = [];

    /**
     * @var \Netdudes\DataSourceryBundle\DataSource\Configuration\Field[]
     */
    private $queryBuilderDataSourceFields;

    /**
     * @var array
     */
    private $transformers;

    public function __construct(array $queryBuilderDataSourceFields, array $transformers)
    {
        $this->queryBuilderDataSourceFields = $queryBuilderDataSourceFields;
        $this->transformers = $transformers;
    }

    /**
     * Collect all required fields that *must* be SELECTed
     *
     * @param Query $query
     *
     * @return array
     */
    public function extractRequiredFields(Query $query)
    {
        $uniqueId = spl_object_hash($query);
        if (!isset($this->fieldsCache[$uniqueId])) {
            $queryRequiredFields = $query->extractRequiredFields();
            $transformerRequiredFields = $this->extractTransformerRequiredFields($this->transformers);
            $firstLevelRequiredFields = array_unique(array_merge($queryRequiredFields, $transformerRequiredFields));
            $this->fieldsCache[$uniqueId] = $this->recursivelyAddDependantAliasFields($firstLevelRequiredFields);
        }

        return $this->fieldsCache[$uniqueId];
    }

    /**
     * Finds all required fields that stem from the logic inside transformers
     *
     * @param TransformerInterface[] $transformers
     *
     * @return array
     */
    private function extractTransformerRequiredFields(array $transformers)
    {
        $transformersRequiredFields = [];
        foreach ($transformers as $transformer) {
            foreach ($transformer->getRequiredFieldNames() as $fieldName) {
                if (!in_array($fieldName, $transformersRequiredFields, true)) {
                    $transformersRequiredFields[] = $fieldName;
                }
            }
        }

        return $transformersRequiredFields;
    }

    /**
     * @param array $fieldNames
     *
     * @return array
     * @throws \Exception
     */
    private function recursivelyAddDependantAliasFields(array $fieldNames)
    {
        $dependantFields = [];
        foreach ($fieldNames as $fieldName) {
            $field = $this->getField($fieldName);
            if ($field && is_array($field->getDatabaseSelectAlias())) {
                foreach ($field->getDatabaseSelectAlias() as $secondLevelRequiredField) {
                    $dependantFields[] = $secondLevelRequiredField;
                }
            }
        }

        $newFieldsHaveBeenFound = count($dependantFields) != count(array_intersect($fieldNames, $dependantFields));
        if ($newFieldsHaveBeenFound) {
            return $this->recursivelyAddDependantAliasFields(array_unique(array_merge($fieldNames, $dependantFields)));
        }

        return $fieldNames;
    }

    /**
     * @param $fieldName
     *
     * @return Field
     */
    private function getField($fieldName)
    {
        foreach ($this->queryBuilderDataSourceFields as $queryBuilderDataSourceField) {
            if ($queryBuilderDataSourceField->getUniqueName() == $fieldName) {
                return $queryBuilderDataSourceField;
            }
        }

        return null;
    }
}
