<?php
namespace Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\QueryBuilder;

use Doctrine\ORM\Query\Expr\Select;
use Netdudes\DataSourceryBundle\DataSource\Configuration\Field;
use Netdudes\DataSourceryBundle\Query\Query;

class SelectGenerator
{
    /**
     * @var JoinGenerator
     */
    private $joinsGenerator;

    /**
     * @var RequiredFieldsExtractor
     */
    private $requiredFieldsExtractor;

    /**
     * @var Field[]
     */
    private $queryBuilderDataSourceFields;

    /**
     * @var string
     */
    private $fromAlias;

    /**
     * @var Select[][]
     */
    private $selectFieldMapCache = [];

    /**
     * @var Select[]
     */
    private $selectCache = [];

    /**
     * @param array                   $queryBuilderDataSourceFields
     * @param                         $fromAlias
     * @param JoinGenerator           $joinsGenerator
     * @param RequiredFieldsExtractor $requiredFieldsExtractor
     */
    public function __construct(array $queryBuilderDataSourceFields, $fromAlias, JoinGenerator $joinsGenerator, RequiredFieldsExtractor $requiredFieldsExtractor)
    {
        $this->joinsGenerator = $joinsGenerator;
        $this->requiredFieldsExtractor = $requiredFieldsExtractor;
        $this->queryBuilderDataSourceFields = $queryBuilderDataSourceFields;
        $this->fromAlias = $fromAlias;
    }

    /**
     * Generates the SELECT part of the DQL (Select object) given a Query.
     *
     * @param Query $query
     *
     * @return Select|null
     */
    public function generate(Query $query)
    {
        $uniqueId = spl_object_hash($query);
        if (!isset($this->selectCache[$uniqueId])) {
            $this->selectCache[$uniqueId] = $this->build($query);
        }

        return $this->selectCache[$uniqueId];
    }

    protected function build(Query $query)
    {
        $selectFieldMap = $this->getSelectFieldMap($query);

        $selectStatements = [];
        foreach ($selectFieldMap as $identifier => $selectField) {
            $selectStatements[] = $selectField . ' ' . $identifier;
        }

        if (empty($selectStatements)) {
            return new Select();
        }

        return new Select($selectStatements);
    }

    /**
     * @param Query $query
     *
     * @return string[]
     */
    public function getSelectFieldMap(Query $query)
    {
        $uniqueId = spl_object_hash($query);
        if (!isset($this->selectFieldMapCache[$uniqueId])) {
            $this->selectFieldMapCache[$uniqueId] = $this->buildSelectFieldMap($query);
        }

        return $this->selectFieldMapCache[$uniqueId];
    }

    /**
     * @param Query $query
     *
     * @return array
     */
    public function getUniqueNameToSelectFieldMap(Query $query)
    {
        $selectFieldMap = $this->getSelectFieldMap($query);
        $uniqueNameToSelectFieldMap = [];
        foreach ($this->queryBuilderDataSourceFields as $field) {
            $alias = $field->getDatabaseSelectAlias();
            if (is_array($alias)) {
                $alias = str_replace('.', '_', $field->getDatabaseFilterQueryField());
            }
            if (array_key_exists($alias, $selectFieldMap)) {
                $uniqueNameToSelectFieldMap[$field->getUniqueName()] = $selectFieldMap[$alias];
            }
        }

        return $uniqueNameToSelectFieldMap;
    }

    /**
     * Builds a map relating the database field alias (the alias in the SELECT statement) and the
     * actual entity field (as in JOINED_ENTITY.field or FROM_ENTITY.field) in order to use them
     * within the WHERE statement.
     *
     * @param Query $query
     *
     * @return array
     */
    protected function buildSelectFieldMap(Query $query)
    {
        $selectFieldsMap = [];
        $requiredFields = $this->requiredFieldsExtractor->extractRequiredFields($query);
        $joins = $this->joinsGenerator->generate($query);
        foreach ($this->queryBuilderDataSourceFields as $element) {
            if (!in_array($element->getUniqueName(), $requiredFields, true)) {
                continue;
            }
            if (is_null($element->getDatabaseFilterQueryField())) {
                continue;
            }
            $fieldIdentifier = $element->getDatabaseSelectAlias();
            if (is_array($fieldIdentifier)) {
                $fieldIdentifier = str_replace('.', '_', $element->getDatabaseFilterQueryField());
            }
            $fieldParts = explode('.', $element->getDatabaseFilterQueryField());
            if (count($fieldParts) === 1) {
                $selectFieldsMap[$fieldIdentifier] = $this->fromAlias . '.' . array_slice($fieldParts, -1, 1)[0];
            } else {
                $joinField = implode('.', array_slice($fieldParts, 0, -1));
                foreach ($joins as $path => $join) {
                    if ($path == $joinField) {
                        $selectFieldsMap[$fieldIdentifier] = $join->getAlias() . '.' . array_slice($fieldParts, -1, 1)[0];
                        break;
                    }
                }
            }
        }

        return $selectFieldsMap;
    }
}
