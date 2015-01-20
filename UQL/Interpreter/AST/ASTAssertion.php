<?php

namespace Netdudes\DataSourceryBundle\UQL\Interpreter\AST;

/**
 * Class ASTAssertion
 *
 * Abstract Syntax Tree node representing a combination a <assertion> on the language.
 *
 * @package Netdudes\NetdudesDataSourceryBundle\UQL\AST
 */
class ASTAssertion
{
    private $identifier;

    private $operator;

    private $value;

    /**
     * @param $identifier
     * @param $operator
     * @param $value
     */
    public function __construct($identifier, $operator, $value)
    {
        $this->identifier = $identifier;
        $this->operator = $operator;
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param mixed $identifier
     */
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @return mixed
     */
    public function getOperator()
    {
        return $this->operator;
    }

    /**
     * @param mixed $operator
     */
    public function setOperator($operator)
    {
        $this->operator = $operator;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}
