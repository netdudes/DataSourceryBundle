<?php
namespace Netdudes\DataSourceryBundle\DataSource\Configuration;

use Netdudes\DataSourceryBundle\DataType\DataTypeInterface;

interface FieldInterface
{
    /**
     * @return string
     */
    public function getUniqueName();

    /**
     * @return string
     */
    public function getReadableName();

    /**
     * @return mixed[]|null
     */
    public function getChoices();

    /**
     * @return DataTypeInterface
     */
    public function getDataType();

    /**
     * @return string
     */
    public function getDescription();

    /**
     * (PHP 5 &gt;= 5.4.0)<br/>
     * Specify data which should be serialized to JSON
     *
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     *               which is a value of any type other than a resource.
     */
    public function jsonSerialize();

    /**
     * @return array
     */
    public function getMetaData();

    /**
     * @param array $metaData
     */
    public function setMetaData($metaData);
}
