<?php

namespace Netdudes\DataSourceryBundle\Query;

/**
 * Defines a sorting condition of a single column
 */
class SortCondition
{
    const ASC = 'ASC';
    const DESC = 'DESC';

    /**
     * Column identifier to sort by
     *
     * @var string
     */
    private $fieldName;

    /**
     * Method of sorting
     *
     * @var string
     */
    private $method;

    /**
     * Sort direction: ASC or DESC
     *
     * @var string
     */
    private $direction;

    /**
     * @param $fieldName
     * @param $method
     * @param $direction
     */
    public function __construct($fieldName, $method, $direction)
    {
        $this->fieldName = $fieldName;
        $this->method = $method;
        $this->direction = $direction;
    }

    /**
     * @return string
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getDirection()
    {
        return $this->direction;
    }
}
