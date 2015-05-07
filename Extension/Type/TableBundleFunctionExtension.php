<?php

namespace Netdudes\DataSourceryBundle\Extension\Type;

/**
 * Wraps a definition of a callable defined in an extension,
 * available to the TableBundle to be used in UQL, etc.
 */
class TableBundleFunctionExtension implements \JsonSerializable
{
    /**
     * Instance to which this function/method belongs
     */
    private $instance;

    /**
     * Name of the method in the instance where its defined
     *
     * @var string
     */
    private $method;

    /**
     * Name by which this function will be known inside TableBundle/UQL
     *
     * @var string
     */
    private $name;

    public function __construct($name, $instance, $method)
    {
        $this->instance = $instance;
        $this->name = $name;
        $this->method = $method;
    }

    /**
     * @return mixed
     */
    public function getInstance()
    {
        return $this->instance;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Performs the call to the function defined by this FunctionExtension
     *
     * @param $arguments
     *
     * @return mixed
     */
    public function call($arguments)
    {
        return call_user_func_array([$this->instance, $this->method], $arguments);
    }

    /**
     * Returns a readable string representation of the function, of the standard form:
     *
     * functionName(argument1, argument2, [optionalArgument1 = defaultValue, [optionalNullableArgument2]])
     *
     * @return string
     */
    public function __toString()
    {
        $reflection = new \ReflectionMethod($this->instance, $this->method);
        $methodParameters = $reflection
            ->getParameters();

        $optionalArgumentCount = 0;
        $splitStringRepresentation = [];
        foreach ($methodParameters as $parameter) {
            $optional = $parameter->isOptional();
            $name = $parameter->getName();
            if ($optional) {
                $name = '[' . $name;
                $defaultValue = $parameter->getDefaultValue();
                if (!is_null($defaultValue)) {
                    if (is_numeric($defaultValue)) {
                        $defaultValue = intval($defaultValue);
                    }
                    $name .= ' = ' . $defaultValue;
                }

                $optionalArgumentCount++;
            }
            $splitStringRepresentation[] = $name;
        }

        return $this->name . '(' . implode(', ', $splitStringRepresentation) . str_repeat(']', $optionalArgumentCount) . ')';
    }

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     *       which is a value of any type other than a resource.
     */
    function jsonSerialize()
    {
        $json =  [
            'name' => $this->getName(),
            'arguments' => [],
        ];

        $reflection = new \ReflectionMethod($this->instance, $this->method);
        $methodParameters = $reflection
            ->getParameters();

        foreach ($methodParameters as $parameter) {
            $isOptional = $parameter->isOptional();
            $argument = [
                'name' => $parameter->getName(),
                'optional' => $isOptional,
                'default' => null,
            ];

            if ($isOptional) {
                $defaultValue = $parameter->getDefaultValue();
                if (!is_null($defaultValue)) {
                    if (is_numeric($defaultValue)) {
                        $defaultValue = intval($defaultValue);
                    }
                    $argument['default'] = $defaultValue;
                }
            }

            $json['arguments'][] = $argument;
        }

        return $json;
    }
}
