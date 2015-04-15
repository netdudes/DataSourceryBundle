<?php
namespace Netdudes\DataSourceryBundle\UQL;

class Tokens
{

    public static $terminalTokens;

    public static $tokenCategories = [
        'GROUP_START' => ['T_BRACKET_OPEN'],
        'GROUP_END' => ['T_BRACKET_CLOSE'],
        'ARRAY_START' => ['T_ARRAY_OPEN'],
        'ARRAY_END' => ['T_ARRAY_CLOSE'],
        'ARRAY_SEPARATOR' => ['T_ARRAY_SEPARATOR'],
        'OPERATOR' => [
            'T_OP_EQ',
            'T_OP_NEQ',
            'T_OP_LT',
            'T_OP_GT',
            'T_OP_LTE',
            'T_OP_GTE',
            'T_OP_LIKE',
            'T_OP_IN',
        ],
        'LOGIC' => [
            'T_OP_AND',
            'T_OP_OR',
            'T_OP_XOR',
        ],
        'LITERAL' => [
            'T_LITERAL_TRUE',
            'T_LITERAL_FALSE',
            'T_FUNCTION_CALL',
            'T_LITERAL'
        ],
        'IDENTIFIER' => ['T_IDENTIFIER'],
    ];

    public static $tokenCanonicalValues = [
        'T_BRACKET_OPEN' => ['('],
        'T_BRACKET_CLOSE' => [')'],
        'T_ARRAY_OPEN' => ['['],
        'T_ARRAY_CLOSE' => [']'],
        'T_ARRAY_SEPARATOR' => [','],
        'T_OP_NEQ' => ['!='],
        'T_OP_LTE' => ['<='],
        'T_OP_GTE' => ['>='],
        'T_OP_LT' => ['<'],
        'T_OP_GT' => ['>'],
        'T_OP_LIKE' => ['~'],
        'T_OP_EQ' => ['='],
        'T_OP_IN' => ['in'],
        'T_OP_AND' => ['and'],
        'T_OP_OR' => ['or'],
        'T_OP_XOR' => ['xor'],
        'T_LITERAL_FALSE' => ['false'],
        'T_LITERAL_TRUE' => ['true']
    ];

    private static $baseTokens = [
        "/^(\\()/" => "T_BRACKET_OPEN",
        "/^(\\))/" => "T_BRACKET_CLOSE",
        "/^(\\[)/" => "T_ARRAY_OPEN",
        "/^(\\])/" => "T_ARRAY_CLOSE",
        "/^(,)/" => "T_ARRAY_SEPARATOR",
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

    private static $operators = [
        "STRING_NEQ" => ["!=", "<>", "not", "is not"],
        "NUMERIC_NEQ" => ["!=", "<>", "not", "is not"],
        "DATETIME_NEQ" => ["!=", "<>", "not", "is not"],
        "NUMERIC_LTE" => ["<=", "less or equal to"],
        "DATETIME_LTE" => ["<=", "before or on"],
        "NUMERIC_GTE" => [">=", "greater or equal to", "greater or equal than"],
        "DATETIME_GTE" => [">=", "after or on"],
        "NUMERIC_LT" => ["<", "less than"],
        "DATETIME_LT" => ["<", "before"],
        "NUMERIC_GT" => [">", "greater than", "more than"],
        "DATETIME_GT" => [">", "after"],
        "STRING_LIKE" => ["~", "like", "is like"],
        "STRING_EQ" => ["=", "==", "equal to", "equals", "is"],
        "NUMERIC_EQ" => ["=", "==", "equal to", "equals", "is"],
        "DATETIME_EQ" => ["=", "==", "on", "is", "at"],
        "IN" => ["in"]
    ];

    private static $operatorTokenAssociations = [
        "T_OP_NEQ" => ["STRING_NEQ", "NUMERIC_NEQ", "DATETIME_NEQ"],
        "T_OP_EQ" => ["STRING_EQ", "NUMERIC_EQ", "DATETIME_EQ"],
        "T_OP_GTE" => ["NUMERIC_GTE", "DATETIME_GTE"],
        "T_OP_GT" => ["NUMERIC_GT", "DATETIME_GT"],
        "T_OP_LTE" => ["NUMERIC_LTE", "DATETIME_LTE"],
        "T_OP_LT" => ["NUMERIC_LT", "DATETIME_LT"],
        "T_OP_LIKE" => ["STRING_LIKE"],
        "T_OP_IN" => ["IN"]
    ];

    /**
     * @var bool
     */
    private static $initialised = false;

    public static function initialize()
    {
        if (self::$initialised) {
            return;
        }

        $tokens = [];
        foreach (self::$operatorTokenAssociations as $token => $operators) {
            $strings = [];
            foreach ($operators as $operator) {
                $strings = array_merge($strings, self::$operators[$operator]);
            }
            $strings = array_unique($strings);
            usort(
                $strings,
                function ($a, $b) {
                    return strlen($a) - strlen($b);
                }
            );
            $regex = "/^(" . implode("|", $strings) . ")(\s|$)/i";
            $tokens[$regex] = $token;
        }

        self::$terminalTokens = array_merge($tokens, self::$baseTokens);
        self::$initialised = true;
    }
}

// Initialise the static class
Tokens::initialize();