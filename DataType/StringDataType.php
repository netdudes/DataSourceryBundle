<?php

namespace Netdudes\DataSourceryBundle\DataType;

use Netdudes\DataSourceryBundle\Query\FilterCondition;

class StringDataType extends AbstractDataType
{
    /**
     * Return an array of available filter methods
     *
     * @return array
     */
    public function getAvailableFilterMethods()
    {
        return [
            FilterCondition::METHOD_STRING_EQ,
            FilterCondition::METHOD_STRING_NEQ,
            FilterCondition::METHOD_STRING_LIKE,
            FilterCondition::METHOD_IN,
            FilterCondition::METHOD_NIN,
            FilterCondition::METHOD_IS_NULL,
            FilterCondition::METHOD_IS_NNULL,
        ];
    }

    /**
     * Return the identifier of the default sort method
     *
     * @return int
     */
    public function getDefaultFilterMethod()
    {
        return FilterCondition::METHOD_STRING_EQ;
    }

    /**
     * Return the name of the type, unique.
     *
     * @return string
     */
    public function getName()
    {
        return 'string';
    }
}
