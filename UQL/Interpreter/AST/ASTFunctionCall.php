<?php

namespace Netdudes\DataSourceryBundle\UQL\Interpreter\AST;

use Netdudes\DataSourceryBundle\UQL\Interpreter\Exception\UQLInterpreterException;

class ASTFunctionCall
{
    private $functionName;

    /**
     * @var array
     */
    private $arguments;

    /**
     * @param       $functionName
     * @param array $arguments
     */
    public function __construct($functionName, array $arguments)
    {
        $this->functionName = $functionName;
        $this->arguments = $arguments;
    }

    public static function createFromExpression($expressionString)
    {
        $matches = [];
        $isFunctionExpression = preg_match("/^([a-zA-Z0-9]+)\(([^\(\)]*)\)/", $expressionString, $matches);
        if (!$isFunctionExpression) {
            throw new UQLInterpreterException("Unexpected malformated function when trying to extract the parameters.");
        }

        $functionName = $matches[1];
        $arguments = array_filter(array_map('trim', explode(',', $matches[2])));

        return new self($functionName, $arguments);
    }

    /**
     * @return array
     */
    public function getArguments()
    {
        return $this->arguments;
    }

    /**
     * @param array $arguments
     */
    public function setArguments($arguments)
    {
        $this->arguments = $arguments;
    }

    /**
     * @return mixed
     */
    public function getFunctionName()
    {
        return $this->functionName;
    }

    /**
     * @param mixed $functionName
     */
    public function setFunctionName($functionName)
    {
        $this->functionName = $functionName;
    }
}
