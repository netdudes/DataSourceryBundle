<?php
namespace Netdudes\DataSourceryBundle\UQL\Autocomplete;

use Netdudes\DataSourceryBundle\UQL\Exception\Semantic\UqlUnexpectedEndOfExpressionException;
use Netdudes\DataSourceryBundle\UQL\Exception\Semantic\UqlUnexpectedTokenException;
use Netdudes\DataSourceryBundle\UQL\Exception\UQLSyntaxError;
use Netdudes\DataSourceryBundle\UQL\Interpreter;
use Netdudes\DataSourceryBundle\UQL\Parser;
use Netdudes\DataSourceryBundle\UQL\Tokens;

class PredictionEngine
{

    private $uqlParser;

    public function __construct()
    {
        $this->uqlParser = new Parser();
    }

    public function predictNextValidTokens($uql)
    {
        $nextTokenCategories = $this->predictNextTokenCategories($uql);
        return $this->getTokensFromCategories($nextTokenCategories);
    }

    private function predictNextTokenCategories($uql)
    {
        try {
            $this->uqlParser->parse($uql);
        } catch (UqlUnexpectedEndOfExpressionException $exception) {
            return $exception->getExpectedTokenCategories();
        } catch (UqlUnexpectedTokenException $exception) {
            return $exception->getExpectedTokenCategories();
        }

        if (count(trim($uql))) {
            return ['LOGIC'];
        }

        return ['IDENTIFIER'];
    }

    private function getTokensFromCategories(array $categories)
    {
        $allCategories = Tokens::$tokenCategories;
        $matchingTokens = [];

        foreach ($allCategories as $category => $tokens) {
            if (in_array($category, $categories)) {
                $matchingTokens = array_merge($matchingTokens, $tokens);
            }
        }

        return array_unique($matchingTokens);
    }
}