<?php

namespace Netdudes\DataSourceryBundle\DataType;

use Netdudes\DataSourceryBundle\Query\FilterCondition;

class DateDataType extends AbstractDataType
{
    /**
     * Return an array of available filter methods
     *
     * @return array
     */
    public function getAvailableFilterMethods()
    {
        return [
            FilterCondition::METHOD_DATETIME_GT,
            FilterCondition::METHOD_DATETIME_GTE,
            FilterCondition::METHOD_DATETIME_EQ,
            FilterCondition::METHOD_DATETIME_LTE,
            FilterCondition::METHOD_DATETIME_LT,
            FilterCondition::METHOD_DATETIME_NEQ,
            FilterCondition::METHOD_IN,
        ];
    }

    /**
     * Return the identifier of the default sort method
     *
     * @return int
     */
    public function getDefaultFilterMethod()
    {
        return FilterCondition::METHOD_DATETIME_EQ;
    }

    /**
     * Return the name of the type, unique.
     *
     * @return string
     */
    public function getName()
    {
        return 'date';
    }
}
