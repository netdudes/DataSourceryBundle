<?php

namespace Netdudes\DataSourceryBundle\Query\Event;

use Netdudes\DataSourceryBundle\DataType\DataTypeInterface;
use Symfony\Component\EventDispatcher\Event;

class PreFilterConditionCreationEvent extends Event
{
    /**
     * @var DataTypeInterface
     */
    private $dataType;

    /**
     * @var mixed
     */
    private $value;

    /**
     * @param mixed             $valueInDatabase
     * @param DataTypeInterface $dataType
     */
    public function __construct($valueInDatabase, DataTypeInterface $dataType)
    {
        $this->dataType = $dataType;
        $this->value = $valueInDatabase;
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
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @param DataTypeInterface $dataType
     */
    public function setDataType($dataType)
    {
        $this->dataType = $dataType;
    }
}
