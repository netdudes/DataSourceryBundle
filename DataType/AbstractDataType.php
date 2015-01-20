<?php

namespace Netdudes\DataSourceryBundle\DataType;


abstract class AbstractDataType implements DataTypeInterface
{
    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     *               which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        return [
            'default' => $this->getDefaultFilterMethod(),
            'available' => $this->getAvailableFilterMethods(),
            'name' => $this->getName(),
        ];
    }

    public function supports($method)
    {
        return in_array($method, $this->getAvailableFilterMethods(), true);
    }
}
