<?php

namespace Netdudes\DataSourceryBundle\DataType;

use Netdudes\DataSourceryBundle\Query\FilterCondition;

class EntityExistenceDataType extends EntityDataType
{
    public function getDefaultFilterMethod()
    {
        return FilterCondition::METHOD_IS_NULL;
    }

    public function getName()
    {
        return 'exists';
    }
}
