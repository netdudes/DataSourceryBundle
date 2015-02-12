<?php
namespace Netdudes\DataSourceryBundle\UQL\Autocomplete;

class AutocompleteResult implements \JsonSerializable
{

    public $inputUql;
    public $existingUlq;
    public $partialWord;
    public $suggestions;

    public function __construct($inputUql, $existingUlq, $partialWord, $suggestions)
    {
        $this->inputUql = $inputUql;
        $this->existingUlq = $existingUlq;
        $this->partialWord = $partialWord;
        $this->suggestions = $suggestions;
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
        return [
            'inputUql' => $this->inputUql,
            'existingUql' => $this->existingUlq,
            'partialWord' => $this->partialWord,
            'suggestions' => $this->suggestions
        ];
    }}