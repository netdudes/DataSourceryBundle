<?php

namespace Netdudes\DataSourceryBundle\DataType;

use Netdudes\DataSourceryBundle\Query\FilterCondition;

class NumberDataType extends AbstractDataType
{
    /**
     * Return an array of available sorting methods
     *
     * @return array
     */
    public function getAvailableFilterMethods()
    {
        return [
            FilterCondition::METHOD_NUMERIC_GT,
            FilterCondition::METHOD_NUMERIC_GTE,
            FilterCondition::METHOD_NUMERIC_EQ,
            FilterCondition::METHOD_NUMERIC_LTE,
            FilterCondition::METHOD_NUMERIC_LT,
            FilterCondition::METHOD_NUMERIC_NEQ,
            FilterCondition::METHOD_IN,
            FilterCondition::METHOD_NIN,
        ];
    }

    /**
     * Return the identifier of the default sort method
     *
     * @return int
     */
    public function getDefaultFilterMethod()
    {
        return FilterCondition::METHOD_NUMERIC_EQ;
    }

    /**
     * Return the name of the type, unique.
     *
     * @return string
     */
    public function getName()
    {
        return 'number';
    }
}
