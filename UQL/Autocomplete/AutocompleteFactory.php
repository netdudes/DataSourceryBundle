<?php
namespace Netdudes\DataSourceryBundle\UQL\Autocomplete;

use Netdudes\DataSourceryBundle\DataSource\Configuration\DataSourceConfigurationInterface;
use Netdudes\DataSourceryBundle\DataSource\DataSourceFactory;
use Netdudes\DataSourceryBundle\DataSource\DataSourceInterface;
use Netdudes\DataSourceryBundle\Extension\TableBundleExtensionContainer;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;

class AutocompleteFactory {

    /**
     * @var TableBundleExtensionContainer
     */
    private $extensionContainer;

    /**
     * @var PredictionEngine
     */
    private $engine;

    /**
     * @var bool
     */
    private $caseSensitive;

    /**
     * @var DataSourceFactory
     */
    private $dataSourceFactory;

    /**
     * @param TableBundleExtensionContainer $extensionContainer
     * @param PredictionEngine              $engine
     * @param DataSourceFactory             $dataSourceFactory
     * @param bool                          $caseSensitive
     */
    public function __construct(TableBundleExtensionContainer $extensionContainer, PredictionEngine $engine, DataSourceFactory $dataSourceFactory, $caseSensitive = true)
    {

        $this->extensionContainer = $extensionContainer;
        $this->engine = $engine;
        $this->caseSensitive = $caseSensitive;
        $this->dataSourceFactory = $dataSourceFactory;
    }

    /**
     * @param $dataSourceEntityOrConfiguration
     *
     * @return Autocomplete
     *
     */
    public function create($dataSourceEntityOrConfiguration)
    {
        if ($dataSourceEntityOrConfiguration instanceof DataSourceInterface) {
            return new Autocomplete($dataSourceEntityOrConfiguration, $this->extensionContainer, $this->engine, $this->caseSensitive);
        }

        if ($dataSourceEntityOrConfiguration instanceof DataSourceConfigurationInterface) {
            $dataSource = $this->dataSourceFactory->createFromConfiguration($dataSourceEntityOrConfiguration);
            return new Autocomplete($dataSource, $this->extensionContainer, $this->engine, $this->caseSensitive);
        }

        throw new InvalidArgumentException("Argument 1 passed to " . __METHOD__ . " must be an instance of DataSourceInterface or DataSourceConfigurationInterface");
    }
}