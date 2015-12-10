<?php

namespace Netdudes\DataSourceryBundle\DataSource\Configuration;

use Netdudes\DataSourceryBundle\DataSource\Configuration\FieldInterface;
use Netdudes\DataSourceryBundle\DataType\DataTypeInterface;

/**
 * This is an extension of the DataSourceField with the specific information needed for
 * the implementation of a DataSource backed by a QueryBuilder-constructing Doctrine-based
 * database source.
 */
class Field implements FieldInterface
{
    /**
     * @var string
     */
    protected $uniqueName;

    /**
     * @var string
     */
    protected $readableName;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var \Netdudes\DataSourceryBundle\DataType\DataTypeInterface
     */
    protected $dataType;

    /**
     * Arbitrary metadata to be passed around for the front-end
     * when this instance is JSON-serialized.
     *
     * @var array
     */
    protected $metaData;

    /**
     * Returns, if it applies, an array of possible choices for this DataSourceField. This allows for
     * complex UXs to be implemented, such as intelligent predictions and autocompletes.
     *
     * @var null|mixed[]
     */
    protected $choices = null;

    /**
     * @var string
     */
    private $databaseFilterQueryField;

    /**
     * @var string|string[]
     */
    private $databaseSelectAlias;

    /**
     * @param                   $uniqueName
     * @param                   $readableName
     * @param                   $description
     * @param DataTypeInterface $dataType
     * @param                   $databaseFilterQueryField
     * @param null              $databaseSelectAlias
     * @param array             $choices
     */
    public function __construct(
        $uniqueName,
        $readableName,
        $description,
        DataTypeInterface $dataType,
        $databaseFilterQueryField = null,
        $databaseSelectAlias = null,
        array $choices = null
    ) {
        $this->uniqueName = $uniqueName;
        $this->description = $description;
        $this->dataType = $dataType;
        $this->readableName = $readableName;
        $this->metaData = [];
        $this->databaseFilterQueryField = $databaseFilterQueryField;
        $this->databaseSelectAlias = $databaseSelectAlias;
        if (!is_null($choices)) {
            $this->choices = $choices;
        }
    }

    /**
     * @return string
     */
    public function getUniqueName()
    {
        return $this->uniqueName;
    }

    /**
     * @return string
     */
    public function getReadableName()
    {
        return $this->readableName;
    }

    /**
     * @return mixed[]|null
     */
    public function getChoices()
    {
        return $this->choices;
    }

    /**
     * @return DataTypeInterface
     */
    public function getDataType()
    {
        return $this->dataType;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

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
        $serialization = [
            'uniqueName' => $this->uniqueName,
            'readableName' => $this->readableName,
            'description' => $this->description,
            'type' => $this->dataType->jsonSerialize(),
            'metadata' => $this->metaData,
        ];

        if (!is_null($this->choices)) {
            $serialization['choices'] = $this->choices;
        }

        return $serialization;
    }

    /**
     * @return array
     */
    public function getMetaData()
    {
        return $this->metaData;
    }

    /**
     * @param array $metaData
     */
    public function setMetaData($metaData)
    {
        $this->metaData = $metaData;
    }

    /**
     * Returns the string alias or array combination of aliases to retrieve the data from the database
     *
     * @return string|string[]
     */
    public function getDatabaseSelectAlias()
    {
        return is_null($this->databaseSelectAlias) ? str_replace('.', '_', $this->databaseFilterQueryField) : $this->databaseSelectAlias;
    }

    /**
     * Returns the database query field used for filtering and searching
     *
     * @return string
     */
    public function getDatabaseFilterQueryField()
    {
        return $this->databaseFilterQueryField;
    }
}
