<?php
namespace Netdudes\DataSourceryBundle\UQL\AST;

/**
 * Class ASTGroup
 *
 * Abstract Syntax Tree node representing a Group of <assertions> with a <logic> between them.
 */
class ASTGroup
{
    private $logic;

    private $elements;

    /**
     * @param $logic
     * @param $elements
     */
    public function __construct($logic, $elements)
    {
        $this->logic = $logic;
        $this->elements = $elements;
    }

    /**
     * $elements can be an array out of ASTGroup and ASTAssertion
     *
     * @return array
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * $elements can be an array out of ASTGroup and ASTAssertion
     *
     * @param array $elements
     */
    public function setElements($elements)
    {
        $this->elements = $elements;
    }

    /**
     * @return string
     */
    public function getLogic()
    {
        return $this->logic;
    }

    /**
     * @param string $logic
     */
    public function setLogic($logic)
    {
        $this->logic = $logic;
    }
}
