<?php
namespace Netdudes\DataSourceryBundle\UQL\Exception\Semantic;

use Netdudes\DataSourceryBundle\UQL\Exception\UQLSyntaxError;

class UqlUnexpectedEndOfExpressionException extends UQLSyntaxError
{
    /**
     * @var string[]
     */
    private $expectedTokenCategories;

    /**
     * @var array
     */
    private $parsedTokenStream;

    /**
     * @param array $expectedTokenCategories
     * @param array $parsedTokenStream
     * @param null  $message
     * @param null  $previous
     */
    public function __construct(array $expectedTokenCategories, array $parsedTokenStream, $message = null, $previous = null)
    {
        $this->expectedTokenCategories = $expectedTokenCategories;

        $message = $message ?: "Unexpected end of expression. Expected token of one of type: " . implode(', ', $expectedTokenCategories);

        parent::__construct($message);
        $this->parsedTokenStream = $parsedTokenStream;
    }

    /**
     * @return \string[]
     */
    public function getExpectedTokenCategories()
    {
        return $this->expectedTokenCategories;
    }

    /**
     * @return array
     */
    public function getParsedTokenStream()
    {
        return $this->parsedTokenStream;
    }
}
