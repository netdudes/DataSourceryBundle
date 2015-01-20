<?php

namespace Netdudes\DataSourceryBundle\UQL\Interpreter\AST;

class ASTArray
{
    /**
     * @var array $elements
     */
    private $elements;

    /**
     * @param array $elements
     */
    public function __construct(array $elements = [])
    {
        $this->elements = $elements;
    }

    /**
     * @return array
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * @param array $elements
     */
    public function setElements(array $elements)
    {
        $this->elements = $elements;
    }

    public function addElement($element)
    {
        $this->elements[] = $element;
    }
}
