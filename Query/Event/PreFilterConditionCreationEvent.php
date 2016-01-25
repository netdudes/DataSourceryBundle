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
     * @param mixed             $value
     * @param DataTypeInterface $dataType
     */
    public function __construct($value, DataTypeInterface $dataType)
    {
        $this->dataType = $dataType;
        $this->value = $value;
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
