<?php

namespace Netdudes\DataSourceryBundle\DataType;

interface DataTypeInterface extends \JsonSerializable
{
    /**
     * Return an array of available sorting methods
     *
     * @return array
     */
    public function getAvailableFilterMethods();

    /**
     * Return the identifier of the default sort method
     *
     * @return int
     */
    public function getDefaultFilterMethod();

    /**
     * Return the name of the type, unique.
     *
     * @return string
     */
    public function getName();

    public function supports($method);
}
