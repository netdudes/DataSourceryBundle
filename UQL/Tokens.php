<?php
namespace Netdudes\DataSourceryBundle\UQL;

class Tokens {

    /**
     * Terminal tokens of the language defined as ordered non-ambiguous
     * regular expression matches linked to specific token unique identifiers.
     */
    public static $terminalTokens = [
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

    public static $tokenCategories =  [
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
}