<?php
namespace Netdudes\DataSourceryBundle\UQL\Exception\Semantic;

use Netdudes\DataSourceryBundle\UQL\Exception\UQLSyntaxError;

class UqlUnexpectedTokenException extends UQLSyntaxError
{
    /**
     * @var array
     */
    private $expectedTokenCategories;

    /**
     * @var array
     */
    private $parsedTokenStream;

    /**
     * @param string $unexpectedTokenName
     * @param int $unexpectedTokenValue
     * @param array $expectedTokenCategories
     * @param array $parsedTokenStream
     * @param string|null $message
     */
    public function __construct($unexpectedTokenName, $unexpectedTokenValue, array $expectedTokenCategories, array $parsedTokenStream, $message = null)
    {
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
