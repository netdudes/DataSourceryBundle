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
     * @var string
     */
    private $method;

    /**
     * @param DataTypeInterface $dataType
     * @param mixed             $value
     * @param string            $method
     */
    public function __construct(DataTypeInterface $dataType, $value, $method)
    {
        $this->dataType = $dataType;
        $this->databaseValue = $value;
        $this->method = $method;
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

    /**
     * @return string
     */
    public function getMethod()
    {
        return $this->method;
    }
}
