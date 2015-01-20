<?php
namespace Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\QueryBuilder;

use Doctrine\ORM\Query\Expr\Join;
use Netdudes\DataSourceryBundle\DataSource\Configuration\Field;
use Netdudes\DataSourceryBundle\Query\Query;

class JoinGenerator
{
    /**
     * @var array
     */
    private $uniqueJoinNameCache = [];

    /**
     * @var Field[]
     */
    private $queryBuilderDataSourceFields;

    /**
     * @var Join[]
     */
    private $joins;

    /**
     * @var string
     */
    private $fromAlias;

    /**
     * @var Join[][]
     */
    private $joinCache = [];

    /**
     * @param array                   $queryBuilderDataSourceFields
     * @param                         $fromAlias
     * @param RequiredFieldsExtractor $requiredFieldsExtractor
     */
    public function __construct(array $queryBuilderDataSourceFields, $fromAlias, RequiredFieldsExtractor $requiredFieldsExtractor)
    {
        $this->requiredFieldsExtractor = $requiredFieldsExtractor;
        $this->queryBuilderDataSourceFields = $queryBuilderDataSourceFields;
        $this->fromAlias = $fromAlias;
    }

    /**
     * Generate the needed joins given a $query. This function will build a dependency tree and
     * walk it recursively to generate an ordered list of Join statements.
     *
     * @param Query $query
     *
     * @return Join[]
     */
    public function generate(Query $query)
    {
        $uniqueId = spl_object_hash($query);
        if (!isset($this->joinCache[$uniqueId])) {
            $this->joinCache[$uniqueId] = $this->build($query);
        }

        return $this->joinCache[$uniqueId];
    }

    /**
     * @param Query $query
     *
     * @return Join[]
     */
    protected function build(Query $query)
    {
        $elements = [];
        $requiredFields = $this->requiredFieldsExtractor->extractRequiredFields($query);
        foreach ($this->queryBuilderDataSourceFields as $field) {
            if (!is_null($query) && !in_array($field->getUniqueName(), $requiredFields, true)) {
                continue;
            }
            if (!is_null($field->getDatabaseFilterQueryField())) {
                $elements[] = $field->getDatabaseFilterQueryField();
            }
        }
        $tree = $this->generateJoinDependencyTree($elements);
        $this->joins = [];
        foreach ($tree as $child => $grandChildren) {
            $this->walkDependencyTreeNode($this->fromAlias, $child, $grandChildren);
        }

        return $this->joins;
    }

    /**
     * Builds a tree of dependencies between entity fields, relating what joins
     * are needed for each selected field in the query.
     *
     * This action is performed recursively.
     *
     * @param array $elements
     *
     * @return array
     */
    protected function generateJoinDependencyTree(array $elements)
    {
        $subtree = [];
        foreach ($elements as $element) {
            $parts = explode('.', $element, 2);
            if (count($parts) == 1) {
                continue;
            }
            if (!isset($subtree[$parts[0]])) {
                $subtree[$parts[0]] = [];
            }
            $subtree[$parts[0]][] = $parts[1];
        }
        foreach ($subtree as $key => $elements) {
            $subtree[$key] = $this->generateJoinDependencyTree($elements);
        }

        return $subtree;
    }

    /**
     * Walks a node of the dependency tree, recursively generating an ordered list on Joins
     * that is stored in the $this->joins cache.
     *
     * @param       $parentUniqueIdentifier
     * @param       $node
     * @param       $descendants
     * @param array $completePath
     */
    protected function walkDependencyTreeNode($parentUniqueIdentifier, $node, $descendants, $completePath = [])
    {
        $completePath[] = $node;
        $joinedCompletePath = implode('.', $completePath);
        $joinUniqueIdentifier =
            array_key_exists($joinedCompletePath, $this->uniqueJoinNameCache) ?
                $this->uniqueJoinNameCache[$joinedCompletePath] :
                ($this->uniqueJoinNameCache[$joinedCompletePath] = uniqid("JOIN_${node}_"));
        $this->joins[$joinedCompletePath] = new Join(Join::LEFT_JOIN, $parentUniqueIdentifier . '.' . $node, $joinUniqueIdentifier);
        foreach ($descendants as $child => $grandChildren) {
            $this->walkDependencyTreeNode($joinUniqueIdentifier, $child, $grandChildren, $completePath);
        }
    }
}
