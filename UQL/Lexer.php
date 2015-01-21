<?php

namespace Netdudes\DataSourceryBundle\UQL;

use Netdudes\DataSourceryBundle\UQL\Exception\UqlLexerException;

/**
 * Class Lexer
 *
 * The Lexer component has the task of translating the user-typed syntax into
 * concrete Tokens with definite meanings in the language's syntax.
 *
 * @package Netdudes\NetdudesDataSourceryBundle\UQL
 */
class Lexer
{
    /**
     * Terminal tokens of the language defined as ordered non-ambiguous
     * regular expression matches linked to specific token unique identifiers.
     */
    protected static $terminalTokens = [
        "/^(\\()/" => "T_BRACKET_OPEN",
        "/^(\\))/" => "T_BRACKET_CLOSE",
        "/^(\\[)/" => "T_ARRAY_OPEN",
        "/^(\\])/" => "T_ARRAY_CLOSE",
        "/^(,)/" => "T_ARRAY_SEPARATOR",
        "/^(not|is not equal to|is not|different to)(\s|$)/i" => "T_OP_NEQ",
        "/^(!=|<>)/i" => "T_OP_NEQ",
        "/^(less or equal to|less or equal|before or on)(\s|$)/i" => "T_OP_LTE",
        "/^(<=)/i" => "T_OP_LTE",
        "/^(greater or equal to|greater or equal|more or equal to|more or equal|after or on)(\s|$)/i" => "T_OP_GTE",
        "/^(>=)/i" => "T_OP_GTE",
        "/^(less than|less|before)(\s|$)/i" => "T_OP_LT",
        "/^(<)/i" => "T_OP_LT",
        "/^(larger than|larger|more than|more|after)(\s|$)/i" => "T_OP_GT",
        "/^(>)/i" => "T_OP_GT",
        "/^(like|is like)(\s|$)/i" => "T_OP_LIKE",
        "/^(~)/i" => "T_OP_LIKE",
        "/^(equal to|equals|on|is|at)(\s|$)/i" => "T_OP_EQ",
        "/^(==|=)/i" => "T_OP_EQ",
        "/^(IN)(\s|$)/i" => "T_OP_IN",
        "/^(AND)(\s|$)/i" => "T_LOGIC_AND",
        "/^(XOR)(\s|$)/i" => "T_LOGIC_XOR",
        "/^(OR)(\s|$)/i" => "T_LOGIC_OR",
        "/^(false)(\s|$)/i" => "T_LITERAL_FALSE",
        "/^(true)(\s|$)/i" => "T_LITERAL_TRUE",
        "/^([a-zA-Z0-9]+\([^\(\)]*\))/" => "T_FUNCTION_CALL",
        "/^([0-9]+|'[^']*'|\"[^\"]*\")/" => "T_LITERAL",
        "/^([a-zA-Z\\_]+)/" => "T_IDENTIFIER",
        "/^(\\s+)/" => "T_WHITESPACE",
    ];

    /**
     * Lex the input string by moving through it trying to match any of
     * the defined language tokens.
     *
     * @param $string
     *
     * @return array
     * @throws \Exception
     */
    public static function lex($string)
    {
        $tokens = [];
        $cursor = 0;

        while ($cursor < strlen($string)) {
            $result = static::matchToken($string, $cursor);
            if ($result === false) {
                throw new UqlLexerException('Can\'t parse at character ' . $cursor . ': "' . substr($string, $cursor, 50) . '[...]"');
            } elseif ($result['token'] != "T_WHITESPACE") {
                // We found a non-whitespace token. Store it.
                $tokens[] = $result;
            }
            $cursor += strlen($result['match']);
        }

        return $tokens;
    }

    /**
     * Tries to match any of the tokens to the current cursor position.
     *
     * @param $string
     * @param $cursor
     *
     * @return array|bool
     */
    public static function matchToken($string, $cursor)
    {
        $string = substr($string, $cursor);
        foreach (static::$terminalTokens as $regex => $token) {
            if (preg_match($regex, $string, $matches)) {
                return [
                    'match' => $matches[1],
                    'token' => $token,
                ];
            }
        }

        return false;
    }
}
