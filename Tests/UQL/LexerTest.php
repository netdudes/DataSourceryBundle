<?php

namespace Netdudes\DataSourceryBundle\Tests\UQL;

use Netdudes\DataSourceryBundle\UQL\Lexer;
use PHPUnit\Framework\TestCase;

class LexerTest extends TestCase
{
    /**
     * @dataProvider provideValidStringAndToken
     */
    public function testTokenMatching($string, $token)
    {
        $tokenResult = Lexer::matchToken($string, 0);
        $this->assertEquals($token, $tokenResult['token'], "Failed to match '$string' to token $token");
    }

    /**
     * @dataProvider provideValidStringAndToken
     */
    public function testTokenMatchingInUppercase($string, $token)
    {
        $string = strtoupper($string);

        $tokenResult = Lexer::matchToken($string, 0);
        $this->assertEquals($token, $tokenResult['token'], "Failed to match '$string' to token $token");
    }

    public function testTokenNotMatching()
    {
        $string = '\'this literal has mismatching quotes"';

        $this->assertFalse(Lexer::matchToken($string, 0), "Lexer should return false for string [$string]");
    }

    /**
     * @return array
     */
    public function provideValidStringAndToken()
    {
        return [
            ['(', 'T_BRACKET_OPEN'],
            [')', 'T_BRACKET_CLOSE'],
            ["!=", 'T_OP_NEQ'],
            ["not", 'T_OP_NEQ'],
            ["is not", 'T_OP_NEQ'],
            ["<=", 'T_OP_LTE'],
            [">=", 'T_OP_GTE'],
            ["<", 'T_OP_LT'],
            ["less than", 'T_OP_LT'],
            ["before", 'T_OP_LT'],
            ["=", 'T_OP_EQ'],
            ["equals", 'T_OP_EQ'],
            ["on", 'T_OP_EQ'],
            ["is", 'T_OP_EQ'],
            [">", 'T_OP_GT'],
            ["more than", 'T_OP_GT'],
            ["after", 'T_OP_GT'],
            ["in", 'T_OP_IN'],
            ["not in", 'T_OP_NIN'],
            ["and", 'T_LOGIC_AND'],
            ["or", 'T_LOGIC_OR'],
            ["xor", 'T_LOGIC_XOR'],
            ["'hello there 123 {}'", 'T_LITERAL'],
            ['"this is a random . = string"', 'T_LITERAL'],
            ["someIndentifier", 'T_IDENTIFIER'],
            ["aRandomThingWith_underscores", 'T_IDENTIFIER'],
            ["isNotAnEqualsOperator", 'T_IDENTIFIER'],
            ["falseIsNotActuallyFalse", 'T_IDENTIFIER'],
            [" ", 'T_WHITESPACE'],
            ["      ", 'T_WHITESPACE'],
            ["\n", 'T_WHITESPACE'],
            ["\t", 'T_WHITESPACE'],
        ];
    }
}
