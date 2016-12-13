<?php

namespace Netdudes\DataSourceryBundle\Tests\UQL;

use Netdudes\DataSourceryBundle\UQL\AST\ASTArray;
use Netdudes\DataSourceryBundle\UQL\AST\ASTAssertion;
use Netdudes\DataSourceryBundle\UQL\AST\ASTGroup;
use Netdudes\DataSourceryBundle\UQL\Exception\UQLSyntaxError;
use Netdudes\DataSourceryBundle\UQL\Lexer;
use Netdudes\DataSourceryBundle\UQL\Parser;

class ParserTest extends \PHPUnit_Framework_TestCase
{
    public function testMatchOperator()
    {
        $parser = new Parser();
        $tokensToTest = [
            'T_OP_NEQ',
            'T_OP_LTE',
            'T_OP_LT',
            'T_OP_GTE',
            'T_OP_GT',
            'T_OP_EQ',
            'T_OP_IN',
            'T_OP_NIN',
        ];

        // Build a token stream from the list of tokens to test
        $tokenStream = array_map(
            function ($token) {
                return [
                    'token' => $token
                ];
            },
            $tokensToTest
        );

        // Initialise
        $parser->setTokenStream($tokenStream);
        $parser->setTokenIndex(-1);

        // Try them all
        foreach ($tokensToTest as $token) {
            $this->assertEquals($token, $parser->matchOperator()['token'], "Token $token should match as an operator");
        }
    }

    public function testMatchLogic()
    {
        $parser = new Parser();
        $tokensToTest = [
            'T_LOGIC_AND',
            'T_LOGIC_OR',
            'T_LOGIC_XOR',
        ];

        // Build a token stream from the list of tokens to test
        $tokenStream = array_map(
            function ($token) {
                return [
                    'token' => $token
                ];
            },
            $tokensToTest
        );

        // Initialise
        $parser->setTokenStream($tokenStream);
        $parser->setTokenIndex(-1);

        // Try them all
        foreach ($tokensToTest as $token) {
            $this->assertEquals($token, $parser->matchLogic()['token'], "Token $token should match as a logic token");
        }
    }

    public function testMatchAssertion()
    {
        // Case 1: Correct assertion

        $assertionTokenStream = [
            [
                'token' => 'T_IDENTIFIER',
                'match' => 'testIdentifier',
            ],
            [
                'token' => 'T_OP_EQ',
            ],
            [
                'token' => 'T_LITERAL',
                "match" => '"Some Literal"'
            ],
        ];

        $parser = new Parser();
        $parser->setTokenIndex(-1);
        $parser->setTokenStream($assertionTokenStream);
        $result = $parser->matchAssertion();
        $this->assertTrue($result instanceof ASTAssertion, "Result of matchAssertion should be a ASTAssertion");
        $this->assertEquals("testIdentifier", $result->getIdentifier());
        $this->assertEquals('"Some Literal"', $result->getValue());

        // Case 2: Not an assertion (first element not a literal)

        $nonAssertionTokenStream = [
            [
                'token' => 'T_LOGIC_AND',
            ],
        ];

        $parser = new Parser();
        $parser->setTokenIndex(-1);
        $parser->setTokenStream($nonAssertionTokenStream);
        $result = $parser->matchAssertion();
        $this->assertFalse($result, "Result for non-matching assertion should be false");

        $invalidAssertionTokenStream = [
            [
                'token' => 'T_IDENTIFIER',
                'match' => 'valid',
            ],
            [
                'token' => 'T_IDENTIFIER',
                'match' => 'invalid'
            ],
        ];

        // Case 3: Wrongly formatted assertion

        $parser = new Parser();
        $parser->setTokenIndex(-1);
        $parser->setTokenStream($invalidAssertionTokenStream);
        try {
            $parser->matchAssertion();
            $this->fail("Matching invalid assertion token stream should raise UQLSyntaxError");
        } catch (UQLSyntaxError $e) {
            // Caught the exception. Pass the test.
        }

        $invalidAssertionTokenStream = [
            [
                'token' => 'T_IDENTIFIER',
                'match' => 'valid',
            ],
            [
                'token' => 'T_OP_GTE'
            ],
            [
                'token' => 'T_LOGIC_AND',
                'match' => 'invalid'
            ],
        ];

        $parser = new Parser();
        $parser->setTokenIndex(-1);
        $parser->setTokenStream($invalidAssertionTokenStream);
        try {
            $parser->matchAssertion();
            $this->fail("Matching invalid assertion token stream should raise UQLSyntaxError");
        } catch (UQLSyntaxError $e) {
            // Caught the exception. Pass the test.
        }
    }

    public function testMatchStatement()
    {
        // Case 1: Returns a group

        $mockParser = $this->getMockBuilder('Netdudes\DataSourceryBundle\UQL\Parser')
            ->setMethods(['matchGroup'])
            ->getMock();
        $mockParser->expects($this->any())
            ->method('matchGroup')
            ->will($this->returnValue('MockValidGroup'));

        $this->assertEquals('MockValidGroup', $mockParser->matchStatement());

        // Case 2: Returns an assertion

        $mockParser = $this->getMockBuilder('Netdudes\DataSourceryBundle\UQL\Parser')
            ->setMethods(['matchGroup', 'matchAssertion'])
            ->getMock();
        $mockParser->expects($this->any())
            ->method('matchGroup')
            ->will($this->returnValue(false));
        $mockParser->expects($this->any())
            ->method('matchAssertion')
            ->will($this->returnValue('MockValidAssertion'));

        $this->assertEquals('MockValidAssertion', $mockParser->matchStatement());

        // Case 3: Neither, returns false

        $mockParser = $this->getMockBuilder('Netdudes\DataSourceryBundle\UQL\Parser')
            ->setMethods(['matchGroup', 'matchAssertion'])
            ->getMock();
        $mockParser->expects($this->any())
            ->method('matchGroup')
            ->will($this->returnValue(false));
        $mockParser->expects($this->any())
            ->method('matchAssertion')
            ->will($this->returnValue(false));

        $this->assertFalse($mockParser->matchStatement());
    }

    public function testMatchGroup()
    {
        // Case 1: Correct

        $mockParser = $this->getMockBuilder('Netdudes\DataSourceryBundle\UQL\Parser')
            ->setMethods(['matchConcatenation'])
            ->getMock();
        $mockParser->expects($this->any())
            ->method('matchConcatenation')
            ->will($this->returnValue('MockValidConcatenation'));

        $tokenStream = [
            [
                'token' => 'T_BRACKET_OPEN',
            ],
            [
                'token' => 'T_BRACKET_CLOSE'
            ],
        ];

        // Case 2: Not a group (no opening bracket)

        $mockParser->setTokenStream($tokenStream);
        $mockParser->setTokenIndex(-1);
        $this->assertEquals('MockValidConcatenation', $mockParser->matchGroup());

        $nonGroupTokenStream = [
            [
                'token' => 'T_INVALID',
            ],
        ];

        $mockParser->setTokenStream($nonGroupTokenStream);
        $mockParser->setTokenIndex(-1);
        $this->assertFalse($mockParser->matchGroup());

        // Case 3: Non-matching closing bracket. Invalid.

        $invalidGroupTokenStream = [
            [
                'token' => 'T_BRACKET_OPEN',
                'match' => 'valid',
            ],
            [
                'token' => 'T_BRACKET_OPEN',
                'match' => 'invalid'
            ],
        ];

        $mockParser->setTokenStream($invalidGroupTokenStream);
        $mockParser->setTokenIndex(-1);

        try {
            $mockParser->matchGroup();
            $this->fail("Parser should throw UQLSyntaxError on wrongly formatted group");
        } catch (UQLSyntaxError $e) {
            // Caught the exception, the test passes.
        }
    }

    public function testMatchConcatenation()
    {
        // Case 1: There is no statement at the beginning

        $mockParser = $this->getMockBuilder('Netdudes\DataSourceryBundle\UQL\Parser')
            ->setMethods(['matchStatement'])
            ->getMock();
        $mockParser->expects($this->any())
            ->method('matchStatement')
            ->will($this->returnValue(false));

        try {
            $mockParser->matchConcatenation();
            $this->fail('Should fail on testing concatenation if doesn\'t start with a statement');
        } catch (UQLSyntaxError $e) {
            // Caught the exception, pass.
        }

        // Case 2: There is only one element and no logic.

        $mockParser = $this->getMockBuilder('Netdudes\DataSourceryBundle\UQL\Parser')
            ->setMethods(['matchStatement', 'matchLogic'])
            ->getMock();
        $mockParser->expects($this->any())
            ->method('matchStatement')
            ->will($this->returnValue('MockValidStatement'));
        $mockParser->expects($this->any())
            ->method('matchLogic')
            ->will($this->returnValue(false));

        $result = $mockParser->matchConcatenation();
        $this->assertEquals('MockValidStatement', $result, "Concatenations with only one member should return the only statement");

        // Case 3: There isn't a statement after the first logic

        $mockParser = $this->getMockBuilder('Netdudes\DataSourceryBundle\UQL\Parser')
            ->setMethods(['matchStatement', 'matchLogic', 'getCurrentToken'])
            ->getMock();
        $mockParser->expects($this->any())
            ->method('getCurrentToken')
            ->will(
                $this->returnValue(
                    [
                        'token' => 'T_INVALID',
                        'match' => 'invalid',
                    ]
                )
            );
        $mockParser->expects($this->at(0))
            ->method('matchStatement')
            ->will($this->returnValue('MockValidStatement'));
        $mockParser->expects($this->at(2))
            ->method('matchStatement')
            ->will($this->returnValue(false));
        $mockParser->expects($this->any())
            ->method('matchLogic')
            ->will(
                $this->returnValue(
                    [
                        'token' => 'MockValidLogic',
                    ]
                )
            );

        try {
            $mockParser->matchConcatenation();
            $this->fail('Should throw exception if no statement after logic.');
        } catch (UQLSyntaxError $e) {
            // caught. Pass.
        }
        // Case 4: Mismatching logics in concatenation

        $mockParser = $this->getMockBuilder('Netdudes\DataSourceryBundle\UQL\Parser')
            ->setMethods(['matchStatement', 'matchLogic', 'getCurrentToken'])
            ->getMock();
        $mockParser->expects($this->any())
            ->method('getCurrentToken')
            ->will(
                $this->returnValue(
                    [
                        'token' => 'T_INVALID',
                        'match' => 'invalid',
                    ]
                )
            );
        $mockParser->expects($this->any())
            ->method('matchStatement')
            ->will($this->returnValue('MockValidStatement'));
        $mockParser->expects($this->at(0))
            ->method('matchLogic')
            ->will(
                $this->returnValue(
                    [
                        'token' => 'T_LOGIC_AND',
                    ]
                )
            );
        $mockParser->expects($this->at(1))
            ->method('matchLogic')
            ->will(
                $this->returnValue(
                    [
                        'token' => 'T_LOGIC_OR',
                    ]
                )
            );

        try {
            $mockParser->matchConcatenation();
            $this->fail('Should throw exception with mismatching logic in concatenation.');
        } catch (UQLSyntaxError $e) {
            // caught. Pass.
        }

        // Case 5: Correct

        $mockParser = $this->getMockBuilder('Netdudes\DataSourceryBundle\UQL\Parser')
            ->setMethods(['matchStatement', 'matchLogic', 'getCurrentToken'])
            ->getMock();
        $mockParser->expects($this->any())
            ->method('getCurrentToken')
            ->will(
                $this->returnValue(
                    [
                        'token' => 'T_INVALID',
                        'match' => 'invalid',
                    ]
                )
            );
        $mockParser->expects($this->at(0))
            ->method('matchStatement')
            ->will($this->returnValue('MockValidStatement0'));
        $mockParser->expects($this->at(2))
            ->method('matchStatement')
            ->will($this->returnValue('MockValidStatement1'));
        $mockParser->expects($this->at(1))
            ->method('matchLogic')
            ->will(
                $this->returnValue(
                    [
                        'token' => 'T_LOGIC_AND',
                    ]
                )
            );
        $mockParser->expects($this->at(3))
            ->method('matchLogic')
            ->will($this->returnValue(false));

        $result = $mockParser->matchConcatenation();
        $this->assertTrue($result instanceof ASTGroup);
        $this->assertEquals(
            [
                'MockValidStatement0',
                'MockValidStatement1',
            ],
            $result->getElements()
        );
        $this->assertEquals('T_LOGIC_AND', $result->getLogic());
    }

    public function testMatchArray()
    {
        $testArrays = [
            "[1, 2, 3, 4, 5]" => [1, 2, 3, 4, 5],
            "[1, \"abc\", 2, \"def\"]" => [1, "\"abc\"", 2, "\"def\""],
        ];

        $parser = new Parser();
        foreach ($testArrays as $testArray => $expectedResult) {
            $tokenStream = Lexer::lex($testArray);
            $parser->setTokenStream($tokenStream);
            $parser->setTokenIndex(-1);

            $array = $parser->matchArray();

            $this->assertNotEquals($array, false, 'Array should not be false (meaning it did interpret an array)');
            $this->assertTrue($array instanceof ASTArray, 'Arrays should Parse into ASTArrays');
            $this->assertCount(count($expectedResult), $array->getElements(), 'Array doesn\'t match the expected number of items');
            foreach ($array->getElements() as $index => $element) {
                $this->assertEquals($expectedResult[$index], $element, "Element '$element' on index $index doesn't match the expected $expectedResult[$index]");
            }
        }
    }
}
