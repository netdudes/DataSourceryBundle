<?php
namespace Netdudes\DataSourceryBundle\UQL\Autocomplete;

use Netdudes\DataSourceryBundle\DataSource\Configuration\Field;
use Netdudes\DataSourceryBundle\DataSource\DataSourceInterface;
use Netdudes\DataSourceryBundle\Extension\TableBundleExtensionContainer;
use Netdudes\DataSourceryBundle\Extension\Type\TableBundleFunctionExtension;
use Netdudes\DataSourceryBundle\UQL\Tokens;

class Autocomplete
{

    protected $functionNamesCache;

    protected $fieldNamesCache;

    /**
     * @var PredictionEngine
     */
    private $predictionEngine;

    /**
     * @var TableBundleExtensionContainer
     */
    private $extensionContainer;

    /**
     * @var DataSourceInterface
     */
    private $dataSource;

    /**
     * @var bool
     */
    private $caseSensitive;

    /**
     * @param DataSourceInterface           $dataSource
     * @param TableBundleExtensionContainer $extensionContainer
     * @param PredictionEngine              $predictionEngine
     * @param bool                          $caseSensitive
     */
    public function __construct(DataSourceInterface $dataSource, TableBundleExtensionContainer $extensionContainer, PredictionEngine $predictionEngine, $caseSensitive = true)
    {
        $this->predictionEngine = $predictionEngine;
        $this->extensionContainer = $extensionContainer;
        $this->dataSource = $dataSource;
        $this->caseSensitive = $caseSensitive;
    }

    public function autocomplete($uql)
    {
        list($previousUql, $newWord) = $this->splitInput($uql);
        $expectedTokens = $this->predictionEngine->predictNextValidTokens($previousUql);
        $suggestedValues = $this->translateTokens($expectedTokens);

        if (!is_null($newWord)) {
            $suggestedValues = array_values(array_filter(
                $suggestedValues,
                function($value) use ($newWord) {
                    if ($this->caseSensitive) {
                        return strpos($value, $newWord) === 0;
                    }

                    return strpos(strtolower($value), strtolower($newWord)) === 0;
                }
            ));
        }

        return new AutocompleteResult(
            $uql,
            $previousUql,
            $newWord,
            $suggestedValues
        );


    }

    private function translateTokens($expectedTokens)
    {
        $values = array_reduce(
            array_map(
                [$this, 'translateToken'],
                $expectedTokens
            ),
            function (array $accumulator, array $values) {
                return array_merge($accumulator, $values);
            },
            []
        );

        return array_unique($values);
    }

    public function translateToken($token)
    {
        if (array_key_exists($token, Tokens::$tokenCanonicalValues)) {
            return Tokens::$tokenCanonicalValues[$token];
        }

        if ($token === 'T_FUNCTION_CALL') {
            return $this->getFunctionNames();
        }

        if ($token === 'T_IDENTIFIER') {
            return $this->getFieldNames();
        }

        if ($token == 'T_LITERAL_TRUE') {
            return ['true'];
        }

        if ($token == 'T_LITERAL_FALSE') {
            return ['false'];
        }

        return [];
    }

    private function getFunctionNames()
    {
        if (is_null($this->functionNamesCache)) {
            $this->functionNamesCache = array_map(
                function (TableBundleFunctionExtension $extension) {
                    return $extension->getName() . '()';
                },
                $this->extensionContainer->getFunctions()
            );
        }

        return $this->functionNamesCache;
    }

    private function getFieldNames()
    {
        if (is_null($this->fieldNamesCache)) {
            $this->fieldNamesCache = array_map(
                function (Field $field) {
                    return $field->getUniqueName();
                },
                $this->dataSource->getFields()
            );
        }

        return $this->fieldNamesCache;
    }

    private function splitInput($uql)
    {
        preg_match_all('/((?:\"[^\"]*\")|(?:\'[^\']*\')|[^\s]+)/', $uql, $parts);
        $lastNonWhitespaceToken = array_pop($parts[0]);
        $split = explode($lastNonWhitespaceToken, $uql);
        $end = array_pop($split);
        if (strlen($end) > 0) {
            $lastWord = null;
            $existingUql = implode($lastNonWhitespaceToken, $split) . $lastNonWhitespaceToken;
        } else {
            $lastWord = $lastNonWhitespaceToken;
            $existingUql = implode($lastNonWhitespaceToken, $split);
        }

        return [$existingUql, $lastWord];
    }
}