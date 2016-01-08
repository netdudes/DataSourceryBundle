<?php

namespace Netdudes\DataSourceryBundle\Query;

use Netdudes\DataSourceryBundle\Util\AbstractArrayAccessibleCollection;

/**
 * A collection of sorting conditions, defining a complete sort status for a data source
 */
class Sort extends AbstractArrayAccessibleCollection
{
    /**
     * Helper method. Find a sorting by column id.
     *
     * @param $columnId
     *
     * @return SortCondition|null
     */
    public function getByColumnId($columnId)
    {
        /** @var $sort SortCondition */
        foreach ($this as $sort) {
            if ($sort->getFieldName() == $columnId) {
                return $sort;
            }
        }

        return null;
    }
}
