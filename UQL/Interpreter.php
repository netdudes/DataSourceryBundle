<?php
namespace Netdudes\DataSourceryBundle\UQL;

use Netdudes\DataSourceryBundle\DataSource\Configuration\Field;
use Netdudes\DataSourceryBundle\DataSource\Configuration\FieldInterface;
use Netdudes\DataSourceryBundle\DataSource\DataSourceInterface;
use Netdudes\DataSourceryBundle\Extension\UqlExtensionContainer;
use Netdudes\DataSourceryBundle\Query\Filter;
use Netdudes\DataSourceryBundle\Query\FilterCondition;
use Netdudes\DataSourceryBundle\Query\FilterConditionFactory;
use Netdudes\DataSourceryBundle\UQL\AST\ASTArray;
use Netdudes\DataSourceryBundle\UQL\AST\ASTAssertion;
use Netdudes\DataSourceryBundle\UQL\AST\ASTFunctionCall;
use Netdudes\DataSourceryBundle\UQL\AST\ASTGroup;
use Netdudes\DataSourceryBundle\UQL\Exception\UQLInterpreterException;

/**
 * Class Interpreter
 *
 * The Interpreter transforms the generic Abstract Syntax Tree into Filters
 */
class Interpreter
{
    /**
     * @var UqlExtensionContainer
     */
    private $extensionContainer;

    /**
     * @var DataSourceInterface
     */
    private $dataSource;

    /**
     * @var array
     */
    private $dataSourceElements;

    /**
     * @var bool
     */
    private $caseSensitive;

    /**
     * @var FilterConditionFactory
     */
    private $filterConditionFactory;

    /**
     * Constructor needs the columns descriptor to figure out appropriate filtering methods
     * and translate identifiers.
     *
     * @param UqlExtensionContainer  $extensionContainer
     * @param DataSourceInterface    $dataSource
     * @param FilterConditionFactory $filterConditionFactory
     * @param bool                   $caseSensitive
     */
    public function __construct(
        UqlExtensionContainer $extensionContainer,
        DataSourceInterface $dataSource,
        FilterConditionFactory $filterConditionFactory,
        $caseSensitive = true
    ) {
        $this->extensionContainer = $extensionContainer;
        $this->dataSource = $dataSource;
        $this->filterConditionFactory = $filterConditionFactory;
        $this->caseSensitive = $caseSensitive;

        // Cache an array of data sources (name => object pairs) for reference during the interpretation
        $this->dataSourceElements = array_combine(
            array_map(
                function (FieldInterface $element) use ($caseSensitive) {
                    return $caseSensitive ? $element->getUniqueName() : strtolower($element->getUniqueName());
                },
                $this->dataSource->getFields()
            ),
            $this->dataSource->getFields()
        );
    }

    /**
     * Helper method: matches filtering operators to valid UQL operators
     * in order to do Filter to UQL transformations
     *
     * @param $method
     *
     * @throws Exception\UQLInterpreterException
     * @return
     */
    public static function methodToUQLOperator($method)
    {
        $translationMap = [
            FilterCondition::METHOD_STRING_EQ => "=",
            FilterCondition::METHOD_STRING_LIKE => "~",
            FilterCondition::METHOD_STRING_NEQ => "!=",
            FilterCondition::METHOD_NUMERIC_GT => ">",
            FilterCondition::METHOD_NUMERIC_GTE => ">=",
            FilterCondition::METHOD_NUMERIC_EQ => "=",
            FilterCondition::METHOD_NUMERIC_LTE => "<=",
            FilterCondition::METHOD_NUMERIC_LT => "<",
            FilterCondition::METHOD_NUMERIC_NEQ => "!=",
            FilterCondition::METHOD_IN => "IN",
            FilterCondition::METHOD_BOOLEAN => "is",
            FilterCondition::METHOD_IS_NULL => "is null",
            FilterCondition::METHOD_DATETIME_GT => "after",
            FilterCondition::METHOD_DATETIME_GTE => "after or at",
            FilterCondition::METHOD_DATETIME_EQ => "at",
            FilterCondition::METHOD_DATETIME_LTE => "before or at",
            FilterCondition::METHOD_DATETIME_LT => "before",
            FilterCondition::METHOD_DATETIME_NEQ => "not at",
        ];

        if (isset($translationMap[$method])) {
            return $translationMap[$method];
        }

        throw new UQLInterpreterException("Can't translate filtering method '$method'' into a valid UQL operator");
    }

    /**
     * Generate the filter objects corresponding to a UQL string.
     *
     * @param string $uql
     *
     * @return Filter
     */
    public function interpret($uql)
    {
        if (empty(trim($uql))) {
            return new Filter();
        }

        $parser = new Parser();
        $AST = $parser->parse($uql);

        return $this->buildFilter($AST);
    }

    /**
     * Transforms a subtree of the AST into a concrete filter definition.
     * This function recursively builds all sub-trees.
     *
     * @param ASTGroup|ASTAssertion|mixed $astSubtree
     *
     * TODO: make private
     *
     * @return Filter|FilterCondition
     * @throws \Exception
     */
    public function buildFilter($astSubtree)
    {
        if ($astSubtree instanceof ASTGroup) {
            return $this->buildFilterFromASTGroup($astSubtree);
        }

        if ($astSubtree instanceof ASTAssertion) {
            $filterCondition = $this->buildFilterConditionFromASTAssertion($astSubtree);
            // Single filter. Wrap into dummy filter collection for consistency.
            $filter = new Filter();
            $filter[] = $filterCondition;
            return $filter;
        }

        throw new UQLInterpreterException('Unexpected Abstract Syntax Tree element');
    }

    /**
     * Translate <operator> tokens into Filter Methods.
     *
     * @param string         $token
     * @param FieldInterface $dataSourceElement
     *
     * @throws Exception\UQLInterpreterException
     * @return mixed
     */
    public function translateOperator($token, FieldInterface $dataSourceElement)
    {
        $translationTable = [
            "T_OP_LT" => [
                FilterCondition::METHOD_NUMERIC_LT,
                FilterCondition::METHOD_DATETIME_LT,
            ],
            "T_OP_LTE" => [
                FilterCondition::METHOD_NUMERIC_LTE,
                FilterCondition::METHOD_DATETIME_LTE,
            ],
            "T_OP_EQ" => [
                FilterCondition::METHOD_NUMERIC_EQ,
                FilterCondition::METHOD_STRING_EQ,
                FilterCondition::METHOD_DATETIME_EQ,
                FilterCondition::METHOD_BOOLEAN,
            ],
            "T_OP_GTE" => [
                FilterCondition::METHOD_NUMERIC_GTE,
                FilterCondition::METHOD_DATETIME_GTE,
            ],
            "T_OP_GT" => [
                FilterCondition::METHOD_NUMERIC_GT,
                FilterCondition::METHOD_DATETIME_GT,
            ],
            "T_OP_NEQ" => [
                FilterCondition::METHOD_NUMERIC_NEQ,
                FilterCondition::METHOD_STRING_NEQ,
                FilterCondition::METHOD_DATETIME_NEQ,
            ],
            "T_OP_LIKE" => [
                FilterCondition::METHOD_STRING_LIKE,
            ],
            "T_OP_IN" => [
                FilterCondition::METHOD_IN,
            ],
        ];

        if (!isset($translationTable[$token])) {
            throw new UQLInterpreterException('Unable to translate token ' . $token . ' to a valid filtering method. Unknown token.');
        }
        $possibleMethods = $translationTable[$token];

        // See if any of the methods is the default of the data type
        $dataType = $dataSourceElement->getDataType();
        foreach ($possibleMethods as $possibleMethod) {
            if ($possibleMethod === $dataType->getDefaultFilterMethod()) {
                return $possibleMethod;
            }
        }

        // Else, just accept the first one in the available methods
        foreach ($possibleMethods as $possibleMethod) {
            if (in_array($possibleMethod, $dataType->getAvailableFilterMethods())) {
                return $possibleMethod;
            }
        }

        throw new UQLInterpreterException('Unable to translate token ' . $token . ' to a valid filtering method. No methods are valid for the data type "' . $dataType->getName() . '" for data element "' . $dataSourceElement->getUniqueName() . '"');
    }

    /**
     * Translate Lexer <logic> tokens into Filter Condition Types.
     *
     * @param $token
     *
     * @return string
     * @throws \Exception
     */
    private function translateLogic($token)
    {
        $translationTable = [
            "T_LOGIC_AND" => Filter::CONDITION_TYPE_AND,
            "T_LOGIC_OR" => Filter::CONDITION_TYPE_OR,
            "T_LOGIC_XOR" => Filter::CONDITION_TYPE_XOR,
        ];

        if (isset($translationTable[$token])) {
            return $translationTable[$token];
        }

        throw new \Exception('Unable to translate token ' . $token . ' to a valid filter condition type.');
    }

    /**
     * Trim and clean up the value to be set in the filter.
     *
     * @param $value
     *
     * @return mixed
     */
    private function parseValue($value)
    {
        if (is_bool($value)) {
            return $value ? "1" : "0";
        }

        return trim($value, "\"'");
    }

    /**
     * @param ASTFunctionCall $functionCall
     *
     * @return mixed
     * @throws Exception\UQLInterpreterException
     */
    private function callFunction(ASTFunctionCall $functionCall)
    {
        try {
            return $this->extensionContainer->callFunction($functionCall->getFunctionName(), $functionCall->getArguments());
        } catch (\Exception $e) {
            throw new UQLInterpreterException("The execution of function '" . $functionCall->getFunctionName() . "' failed. Please check the arguments are valid. (" . $e->getMessage() . ")");
        }
    }

    /**
     * @param array $elements
     *
     * @return array
     */
    private function parseArray($elements)
    {
        $array = [];
        foreach ($elements as $element) {
            $array[] = $this->parseValue($element);
        }

        return $array;
    }

    /**
     * @param string $identifier
     *
     * @return Field
     * @throws UQLInterpreterException
     */
    private function matchDataSourceElement($identifier)
    {
        if (!$this->caseSensitive) {
            $identifier = strtolower($identifier);
        }

        if (!isset($this->dataSourceElements[$identifier])) {
            throw new UQLInterpreterException('Unknown filtering element "' . $identifier . '"');
        }

        return $this->dataSourceElements[$identifier];
    }

    /**
     * @param ASTAssertion $astSubtree
     *
     * @throws UQLInterpreterException
     *
     * @return array|mixed
     */
    private function getValue(ASTAssertion $astSubtree)
    {
        if ($astSubtree->getValue() instanceof ASTFunctionCall) {
            return $this->callFunction($astSubtree->getValue());
        }

        if ($astSubtree->getOperator() == 'T_OP_IN') {
            if (!($astSubtree->getValue() instanceof ASTArray)) {
                throw new UQLInterpreterException('Only arrays are valid arguments for IN statements');
            }

            return $this->parseArray($astSubtree->getValue()->getElements());
        }

        return $this->parseValue($astSubtree->getValue());
    }

    /**
     * @param ASTAssertion $astSubtree
     *
     * @throws UQLInterpreterException
     *
     * @return FilterCondition
     */
    private function buildFilterConditionFromASTAssertion(ASTAssertion $astSubtree)
    {
        $field = $this->matchDataSourceElement($astSubtree->getIdentifier());
        $method = $this->translateOperator($astSubtree->getOperator(), $field);
        $value = $this->getValue($astSubtree);

        return $this->filterConditionFactory->create($field, $method, $value);
    }

    /**
     * @param ASTGroup $astSubtree
     *
     * @throws UQLInterpreterException
     * @throws \Exception
     *
     * @return Filter
     */
    private function buildFilterFromASTGroup(ASTGroup $astSubtree)
    {
        $filter = new Filter();
        $condition = $this->translateLogic($astSubtree->getLogic());
        $filter->setConditionType($condition);
        foreach ($astSubtree->getElements() as $element) {
            if ($element instanceof ASTGroup) {
                $filter[] = $this->buildFilterFromASTGroup($element);
            }
            if ($element instanceof ASTAssertion) {
                $filter[] = $this->buildFilterConditionFromASTAssertion($element);
            }
        }

        return $filter;
    }
}
