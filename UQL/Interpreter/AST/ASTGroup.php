<?php

namespace Netdudes\DataSourceryBundle\UQL\Interpreter\AST;

/**
 * Class ASTGroup
 *
 * Abstract Syntax Tree node representing a Group of <assertions> with a <logic> between them.
 *
 * @package Netdudes\NetdudesDataSourceryBundle\UQL\AST
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
     * @return mixed
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * @param mixed $elements
     */
    public function setElements($elements)
    {
        $this->elements = $elements;
    }

    /**
     * @return mixed
     */
    public function getLogic()
    {
        return $this->logic;
    }

    /**
     * @param mixed $logic
     */
    public function setLogic($logic)
    {
        $this->logic = $logic;
    }
}
