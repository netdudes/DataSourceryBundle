<?php

namespace Netdudes\DataSourceryBundle\Query;

use Netdudes\DataSourceryBundle\UQL\Interpreter;

/**
 * Defines a filter
 */
class FilterCondition
{
    /**
     * Filter types: standard string and numeric comparison.
     * This set of comparison methods are understood by any DataSource that wants to be easily swappable
     *
     * Additional filtering methods may be defined to be consumed by custom data sources.
     */

    const METHOD_STRING_EQ = "STRING_EQ";
    const METHOD_STRING_LIKE = "STRING_LIKE";
    const METHOD_STRING_NEQ = "STRING_NEQ";
    const METHOD_NUMERIC_GT = "NUMERIC_GT";
    const METHOD_NUMERIC_GTE = "NUMERIC_GTE";
    const METHOD_NUMERIC_EQ = "NUMERIC_EQ";
    const METHOD_NUMERIC_LTE = "NUMERIC_LTE";
    const METHOD_NUMERIC_LT = "NUMERIC_LT";
    const METHOD_NUMERIC_NEQ = "NUMERIC_NEQ";
    const METHOD_IN = "IN";
    const METHOD_BOOLEAN = "BOOLEAN";
    const METHOD_IS_NULL = "IS_NULL";
    const METHOD_DATETIME_GT = "DATETIME_GT";
    const METHOD_DATETIME_GTE = "DATETIME_GTE";
    const METHOD_DATETIME_EQ = "DATETIME_EQ";
    const METHOD_DATETIME_LTE = "DATETIME_LTE";
    const METHOD_DATETIME_LT = "DATETIME_LT";
    const METHOD_DATETIME_NEQ = "DATETIME_NEQ";

    /**
     * Column ID to filter by
     *
     * @var string
     */
    private $fieldName;

    /**
     * Filtering method, of those defined in this class' constants
     *
     * @var string
     */
    private $method;

    /**
     * A value to filter by
     *
     * @var mixed
     */
    private $value;

    /**
     * @var mixed
     */
    private $valueInDatabase;

    /**
     * @param $columnIdentifier  string The unique identifier of the column to filter by
     * @param $method            string A value  passed to the data source in order to decide how to filter
     * @param $value             string The value to filter with
     * @param $valueInDatabase   string The presentation of the filtered value inside the database
     */
    public function __construct($columnIdentifier, $method, $value, $valueInDatabase)
    {
        $this->fieldName = $columnIdentifier;
        $this->method = $method;
        $this->value = $value;
        $this->valueInDatabase = $valueInDatabase;
    }

    /**
     * Returns an array serializable to JSON for the compact exchange format.
     *
     * @return array
     */
    public function toJsonSerializable()
    {
        return [
            'field' => $this->fieldName,
            'method' => $this->method,
            'value' => $this->value
        ];
    }

    /**
     * Get a UQL representation of this filter statement
     *
     * @return string
     */
    public function toUQL()
    {
        $numericMethods = [
            self::METHOD_NUMERIC_EQ,
            self::METHOD_NUMERIC_GT,
            self::METHOD_NUMERIC_GTE,
            self::METHOD_NUMERIC_LT,
            self::METHOD_NUMERIC_LTE,
            self::METHOD_NUMERIC_NEQ,
        ];

        $booleanMethods = [
            self::METHOD_BOOLEAN,
        ];

        $value = $this->getValue();
        if (is_array($value)) {
            // Array value (e.g. a IN statement)
            // Wrap every element in quotes, separate by commas and make into a square-bracket array.
            $value = '[' . implode(
                    ', ',
                    array_map(
                        function ($string) {
                            return "\"$string\"";
                        },
                        $value
                    )
                ) . ']';
        } else {
            if (in_array($this->method, $booleanMethods)) {
                $value = $value ? 'true' : 'false';
            } elseif (in_array($this->method, $numericMethods) && preg_match("/^[0-9]+$/", trim($value))) {
                $value = trim($value);
            } else {
                $value = "\"$value\"";
            }
        }

        return $this->getFieldName() . " " . Interpreter::methodToUQLOperator($this->getMethod()) . " " . $value;
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
     * @return mixed
     */
    public function getFieldName()
    {
        return $this->fieldName;
    }

    /**
     * @param mixed $fieldName
     */
    public function setFieldName($fieldName)
    {
        $this->fieldName = $fieldName;
    }

    /**
     * @return mixed
     */
    public function getMethod()
    {
        return $this->method;
    }

    /**
     * @param mixed $method
     */
    public function setMethod($method)
    {
        $this->method = $method;
    }

    /**
     * @return mixed
     */
    public function getValueInDatabase()
    {
        return $this->valueInDatabase;
    }

    /**
     * @param mixed $valueInDatabase
     */
    public function setValueInDatabase($valueInDatabase)
    {
        $this->valueInDatabase = $valueInDatabase;
    }
}
