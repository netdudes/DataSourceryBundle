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
        foreach (Tokens::$terminalTokens as $regex => $token) {
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
