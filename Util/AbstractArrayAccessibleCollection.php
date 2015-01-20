<?php

namespace Netdudes\DataSourceryBundle\Util;

/**
 * This is a helper abstract class that implements array access ([]) to an underlying
 * set of items.
 */
abstract class AbstractArrayAccessibleCollection implements \Countable, \Iterator, \ArrayAccess, \JsonSerializable
{
    private $_elements = [];

    private $_internalIndex = 0;

    /**
     * @param array $initial
     */
    public function __construct(array $initial = [])
    {
        foreach ($initial as $key => $value) {
            $this[$key] = $value;
        }
    }

    public function offsetGet($offset)
    {
        return $this->_elements[$offset];
    }

    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->_elements[] = $value;
        } else {
            $this->_elements[$offset] = $value;
        }
    }

    public function offsetUnset($offset)
    {
        unset($this->_elements[$offset]);
    }

    public function count()
    {
        return count($this->_elements);
    }

    public function toArray()
    {
        return $this->_elements;
    }

    public function current()
    {
        return $this->_elements[$this->_internalIndex];
    }

    public function next()
    {
        $this->_internalIndex++;
    }

    public function key()
    {
        return $this->_internalIndex;
    }

    public function valid()
    {
        return $this->offsetExists($this->_internalIndex);
    }

    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->_elements);
    }

    public function rewind()
    {
        $this->_internalIndex = 0;
    }

    public function addAtIndex($index, $element)
    {
        array_splice($this->_elements, $index, 0, [$element]);
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     */
    public function jsonSerialize()
    {
        return $this->_elements;
    }
}
