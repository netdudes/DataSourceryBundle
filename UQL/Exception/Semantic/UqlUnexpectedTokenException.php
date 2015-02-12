<?php
namespace Netdudes\DataSourceryBundle\UQL\Exception\Semantic;

use Netdudes\DataSourceryBundle\UQL\Exception\UQLSyntaxError;

class UqlUnexpectedTokenException extends UQLSyntaxError
{
    private $unexpectedTokenName;
    private $unexpectedTokenValue;
    private $expectedTokenCategories;

    /**
     * @var array
     */
    private $parsedTokenStream;

    function __construct($unexpectedTokenName, $unexpectedTokenValue, array $expectedTokenCategories, array $parsedTokenStream, $message = null)
    {
        $this->unexpectedTokenName = $unexpectedTokenName;
        $this->unexpectedTokenValue = $unexpectedTokenValue;
        $this->expectedTokenCategories = $expectedTokenCategories;

        $message = $message ?: 'Unexpected token "' . $unexpectedTokenName . ' (' . $unexpectedTokenValue . ')". ';

        parent::__construct($message);

        $this->parsedTokenStream = $parsedTokenStream;
    }

    /**
     * @return array
     */
    public function getParsedTokenStream()
    {
        return $this->parsedTokenStream;
    }

    /**
     * @return array
     */
    public function getExpectedTokenCategories()
    {
        return $this->expectedTokenCategories;
    }
}