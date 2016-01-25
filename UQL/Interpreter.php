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
 * The Interpreter transforms the generic Abstract Syntax Tree into
 * the specific FilterDefinition elements.
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
     * @param bool                   $caseSensitive
     * @param FilterConditionFactory $filterConditionFactory
     */
    public function __construct(
        UqlExtensionContainer $extensionContainer,
        DataSourceInterface $dataSource,
        FilterConditionFactory $filterConditionFactory,
        $caseSensitive = true
    ) {
        $this->extensionContainer = $extensionContainer;
        $this->dataSource = $dataSource;
        $this->caseSensitive = $caseSensitive;

        $this->filterConditionFactory = $filterConditionFactory;

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
     * @param $UQLStringInput
     *
     * @return Filter
     */
    public function generateFilters($UQLStringInput)
    {
        if (empty(trim($UQLStringInput))) {
            return new Filter();
        }

        // Get the Abstract Syntax Tree of the input from the parser
        $parser = new Parser();
        $AST = $parser->parse($UQLStringInput);

        // Recursively translate into filters.
        $filters = $this->buildFilterLevel($AST);

        if ($filters instanceof FilterCondition) {
            // Single filter. Wrap into dummy filter collection for consistency.
            $filterDefinition = new Filter();
            $filterDefinition[] = $filters;
            $filters = $filterDefinition;
        }

        return $filters;
    }

    /**
     * Transforms a subtree of the AST into a concrete filter definition.
     * This function recursively builds all sub-trees.
     *
     * @param $astSubtree
     *
     * @return Filter|Filter
     * @throws \Exception
     */
    public function buildFilterLevel($astSubtree)
    {
        if ($astSubtree instanceof ASTGroup) {
            $filterDefinition = new Filter();
            $condition = $this->translateLogic($astSubtree->getLogic());
            $filterDefinition->setConditionType($condition);
            foreach ($astSubtree->getElements() as $element) {
                $filterDefinition[] = $this->buildFilterLevel($element);
            }

            return $filterDefinition;
        } elseif ($astSubtree instanceof ASTAssertion) {
            $field = $this->matchDataSourceElement($astSubtree->getIdentifier());
            $method = $this->translateOperator($astSubtree->getOperator(), $field);
            $value = $this->getValue($astSubtree);

            return $this->filterConditionFactory->create($value, $method, $field);
        }

        throw new UQLInterpreterException('Unexpected Abstract Syntax Tree element');
    }

    /**
     * Translate <operator> tokens into Filter Methods.
     *
     * @param                          $token
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
     * @return mixed
     * @throws \Exception
     */
    protected function translateLogic($token)
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
    protected function parseValue($value)
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

    private function parseArray($elements)
    {
        $array = [];
        foreach ($elements as $element) {
            $array[] = $this->parseValue($element);
        }

        return $array;
    }

    /**
     * @param $identifier
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
     * @return array|mixed
     *
     * @throws UQLInterpreterException
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
}
