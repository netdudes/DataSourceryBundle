<?php
namespace Netdudes\DataSourceryBundle\UQL\Event;

use Netdudes\DataSourceryBundle\DataType\DataTypeInterface;
use Symfony\Component\EventDispatcher\Event;

class PreCreateFilterConditionEvent extends Event
{
    /**
     * @var DataTypeInterface
     */
    private $dataType;

    /**
     * @var mixed
     */
    private $databaseValue;

    /**
     * @param DataTypeInterface $dataType
     * @param mixed $value
     */
    public function __construct(DataTypeInterface $dataType, $value)
    {
        $this->dataType = $dataType;
        $this->databaseValue = $value;
    }

    /**
     * @return DataTypeInterface
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * @return mixed
     */
    public function getDatabaseValue()
    {
        return $this->databaseValue;
    }

    /**
     * @param mixed $databaseValue
     */
    public function setDatabaseValue($databaseValue)
    {
        $this->databaseValue = $databaseValue;
    }
}
