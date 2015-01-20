<?php
namespace Netdudes\DataSourceryBundle\Query;

use Netdudes\DataSourceryBundle\Query\Exception\MethodNotAllowedException;

class InvalidFilter extends Filter
{
    /**
     * A code to identify the reason the filter is invalid
     *
     * @var int
     */
    protected $code = 0;

    /**
     * A user-readable message describing the reason the filter is invalid
     *
     * @var string
     */
    protected $message;

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param int $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @param array $message
     * @param int   $code
     */
    public function __construct($message, $code = 0)
    {
        $this->message = $message;
        $this->code = $code;
    }

    /**
     * @param $index
     * @param $value
     *
     * @throws MethodNotAllowedException
     */
    public function addAtIndex($index, $value)
    {
        throw new MethodNotAllowedException(__CLASS__, __METHOD__, "Invalid filters must be empty");
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     *
     * @throws MethodNotAllowedException
     */
    public function offsetSet($offset, $value)
    {
        throw new MethodNotAllowedException(__CLASS__, __METHOD__, "Invalid filters must be empty");
    }
}
