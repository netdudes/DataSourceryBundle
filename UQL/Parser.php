<?php

namespace Netdudes\DataSourceryBundle\UQL;

use Netdudes\DataSourceryBundle\UQL\AST\ASTArray;
use Netdudes\DataSourceryBundle\UQL\AST\ASTAssertion;
use Netdudes\DataSourceryBundle\UQL\AST\ASTFunctionCall;
use Netdudes\DataSourceryBundle\UQL\AST\ASTGroup;
use Netdudes\DataSourceryBundle\UQL\Exception\UQLSyntaxError;

/**
 * Class Parser
 *
 * The parser translates a linear stream of tokens into a logical Abstract
 * Syntax Tree (AST) that represents the logical structure of the language
 * with independence of the actual end-objects (filters).
 *
 * @package Netdudes\NetdudesDataSourceryBundle\UQL
 */
class Parser
{
    /**
     *
     * Grammar (<identifier>s and <literal>s are simple scalars defined by regular expressions on the lexer):
     *
     * <operator>       ::=     "<" | ">" | "<=" | ">=" | "!=" | "<>" | "="
     * <logic>          ::=     "AND" | "OR"
     * <assertion>      ::=     <identifier> <operator> <literal>
     * <concatenation>  ::=     <statement> { <logic> <statement> }
     * <group>          ::=     "(" <concatenation> ")"
     * <statement>      ::=     <assertion> | <group>
     * <query>          ::=     <concatenation>
     *
     */

    private $tokenIndex;

    private $tokenStream;

    public function __construct()
    {
        $this->tokenIndex = -1;
    }

    /**
     * Lex, initialise and return the AST.
     *
     * @param $string
     *
     * @return bool|ASTAssertion|ASTGroup
     */
    public function parse($string)
    {
        $this->tokenStream = Lexer::lex($string);
        $this->tokenIndex = -1;

        return $this->getAST();
    }

    /**
     * Entry point of the grammar parsing.
     *
     * @return bool|ASTAssertion|ASTGroup
     */
    public function getAST()
    {
        // The top-level syntax is, in general, a concatenation of statements with logic connectors.
        $concatenation = $this->matchConcatenation();

        // Make sure we are at the end of the UQL
        $token = $this->nextToken();
        if ($token == false) {
            return $concatenation;
        }

        $this->throwUnexpectedTokenSyntaxError("Logic operator or end of UQL expected after statement in first-level concatenation");
    }

    /**
     * Tries to match the following tokens to a <concatenation> grammar.
     *
     * @return bool|ASTAssertion|ASTGroup
     */
    public function matchConcatenation()
    {
        $elements = [];

        $firstStatement = $this->matchStatement();

        if ($firstStatement === false) {
            $this->throwSyntaxError('Expected statement at beginning of concatenation.');
        }

        $elements[] = $firstStatement;

        $firstLogic = $this->matchLogic();
        if ($firstLogic === false) {
            // There is no actual concatenation. This is a single statement. Return as such.
            return $firstStatement;
        }
        $logic = $firstLogic;

        // While there are concatenating logic operators, keep adding elements.
        while ($logic !== false) {
            if ($logic['token'] != $firstLogic['token']) {
                $this->throwSyntaxError('Can\'t mix ORs and ANDs in same-level expression, ambiguous statement.');
            }
            $statement = $this->matchStatement();
            if ($statement === false) {
                $this->throwSyntaxError('Expected statement after logic operator');
            }
            $elements[] = $statement;
            $logic = $this->matchLogic();
        }

        return new ASTGroup($firstLogic['token'], $elements);
    }

    /**
     * Tries to match a general <statement>, that is a <group> or <assertion>
     *
     * @return bool|ASTAssertion|ASTGroup
     */
    public function matchStatement()
    {
        // Try <group>
        $matchGroup = $this->matchGroup();

        if ($matchGroup !== false) {
            return $matchGroup;
        }

        // Try <assertion>
        $matchAssertion = $this->matchAssertion();

        if ($matchAssertion !== false) {
            return $matchAssertion;
        }

        // None found
        $this->rewindToken();

        return false;
    }

    /**
     * Tries to match a <group> grammar to the following tokens
     *
     * @return bool|ASTAssertion|ASTGroup
     */
    public function matchGroup()
    {
        $token = $this->nextToken();

        // Check for the open parenthesis
        if ($token['token'] != "T_BRACKET_OPEN") {
            $this->rewindToken();

            return false;
        }

        // The interior of a group is a <concatenation>
        $concatenation = $this->matchConcatenation();

        $token = $this->nextToken();

        // Check for closed parenthesis. Mismatch is a Syntax Error.
        if ($token['token'] != "T_BRACKET_CLOSE") {
            $this->throwUnexpectedTokenSyntaxError('Expected T_BRACKET_CLOSE.');
        }

        return $concatenation;
    }

    /**
     * Tries to match the following tokens to an <assertion>.
     *
     * @throws Exception\UQLSyntaxError
     * @return bool|ASTAssertion
     */
    public function matchAssertion()
    {
        $identifier = $this->nextToken();

        if ($identifier['token'] != 'T_IDENTIFIER') {
            // If a stream doesn't start with an identifier, it's not an <assertion>.
            $this->rewindToken();

            return false;
        }

        $operator = $this->matchOperator();

        if ($operator === false) {
            $this->nextToken(); // MatchOperator rewinds
            $this->throwUnexpectedTokenSyntaxError('Comparison operator expected after identifier');
        }

        $array = $this->matchArray();
        if ($array) {
            if ($operator['token'] !== 'T_OP_IN') {
                throw new UQLSyntaxError("Arrays are only valid after operator IN");
            }

            return new ASTAssertion($identifier['match'], $operator['token'], $array);
        }

        $literal = $this->nextToken();

        if ($literal['token'] == 'T_FUNCTION_CALL') {
            return new ASTAssertion($identifier['match'], $operator['token'], ASTFunctionCall::createFromExpression($literal['match']));
        }

        if (strpos($literal['token'], 'T_LITERAL') !== 0) {
            $this->throwUnexpectedTokenSyntaxError('Array, literal or function call expected after comparison operator');
        }
        $literal = $this->transformLiteral($literal);

        return new ASTAssertion($identifier['match'], $operator['token'], $literal['match']);
    }

    /**
     * Tries to match the next token to an <operator>.
     *
     * @return bool
     */
    public function matchOperator()
    {
        $operator = $this->nextToken();

        switch ($operator['token']) {
            case 'T_OP_NEQ':
            case 'T_OP_LTE':
            case 'T_OP_LT':
            case 'T_OP_GTE':
            case 'T_OP_GT':
            case 'T_OP_EQ':
            case 'T_OP_LIKE':
            case 'T_OP_IN':
                return $operator;
                break;
            default:
                $this->rewindToken();

                return false;
        }
    }

    public function matchArray()
    {
        $token = $this->nextToken();
        if ($token['token'] != "T_ARRAY_OPEN") {
            $this->rewindToken();

            return false;
        }

        $element = $this->nextToken();
        if ($element['token'] == "T_ARRAY_CLOSE") {
            // Empty array
            return new ASTArray();
        }

        $elements = [$element['match']];
        $comma = $this->nextToken();
        while ($comma['token'] == "T_ARRAY_SEPARATOR") {
            $element = $this->nextToken();
            if ($element['token'] !== 'T_LITERAL') {
                throw new UQLSyntaxError("An array must consist of literals");
            }
            $elements[] = $element['match'];
            $comma = $this->nextToken();
        }
        if ($comma['token'] != 'T_ARRAY_CLOSE') {
            // Unterminated array
            throw new UQLSyntaxError("Unterminated array.");
        }

        return new ASTArray($elements);
    }

    /**
     * Tries to match the next token to a <logic> operator
     *
     * @return bool
     */
    public function matchLogic()
    {
        $token = $this->nextToken();

        if ($token['token'] == 'T_LOGIC_AND' || $token['token'] == 'T_LOGIC_OR' || $token['token'] == 'T_LOGIC_XOR') {
            return $token;
        }

        // None found
        $this->rewindToken();

        return false;
    }

    /**
     * @return mixed
     */
    public function getTokenStream()
    {
        return $this->tokenStream;
    }

    /**
     * @param mixed $tokenStream
     */
    public function setTokenStream($tokenStream)
    {
        $this->tokenStream = $tokenStream;
    }

    /**
     * @return mixed
     */
    public function getTokenIndex()
    {
        return $this->tokenIndex;
    }

    /**
     * @param mixed $tokenIndex
     */
    public function setTokenIndex($tokenIndex)
    {
        $this->tokenIndex = $tokenIndex;
    }

    /**
     * Advance the token index and return.
     *
     * @return bool
     */
    private function nextToken()
    {
        $this->tokenIndex++;

        return $this->currentToken();
    }

    /**
     * Return the current token, without advancing the index.
     *
     * @return bool
     */
    private function currentToken()
    {
        return isset($this->tokenStream[$this->tokenIndex]) ? $this->tokenStream[$this->tokenIndex] : false;
    }

    /**
     * Move back the token index once.
     */
    private function rewindToken()
    {
        $this->tokenIndex--;
    }

    /**
     * Helper method. Throws an Exception representing a Syntax Error.
     *
     * @param $message
     *
     * @throws \Exception
     */
    private function throwUnexpectedTokenSyntaxError($message)
    {
        if ($this->currentToken() === false) {
            $messageUnexpected = "Unexpected end of expression. ";
        } else {
            $messageUnexpected = 'Unexpected token "' . $this->currentToken()['token'] . ' (' . $this->currentToken()['match'] . ')". ';
        }
        $this->throwSyntaxError($messageUnexpected . $message);
    }

    private function throwSyntaxError($message)
    {
        throw new UQLSyntaxError('Syntax error: ' . $message);
    }

    /**
     * Transforms a literal subtype (e.g. T_LITERAL_FALSE) into a plain
     * literal match. Plain literals are unchanged.
     *
     * @param $literal
     *
     * @return array
     */
    private function transformLiteral($literal)
    {
        switch ($literal['token']) {
            case 'T_LITERAL_FALSE':
                $match = false;
                break;
            case 'T_LITERAL_TRUE':
                $match = true;
                break;
            default:
                $match = $literal['match'];
        }

        return [
            'token' => 'T_LITERAL',
            'match' => $match
        ];
    }
}
