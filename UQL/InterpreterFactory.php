<?php

namespace Netdudes\DataSourceryBundle\UQL;

use Netdudes\DataSourceryBundle\DataSource\DataSourceInterface;
use Netdudes\DataSourceryBundle\Extension\TableBundleExtensionContainer;

class InterpreterFactory
{
    /**
     * @var TableBundleExtensionContainer
     */
    private $extensionContainer;

    /**
     * @var bool
     */
    private $caseSensitive;

    /**
     * @param TableBundleExtensionContainer $extensionContainer
     * @param bool                          $caseSensitive
     */
    public function __construct(TableBundleExtensionContainer $extensionContainer, $caseSensitive = true)
    {
        $this->extensionContainer = $extensionContainer;
        $this->caseSensitive = $caseSensitive;
    }

    /**
     * Builds an instance of the UQL Interpreter
     *
     * @param \Netdudes\DataSourceryBundle\DataSource\DataSourceInterface $dataSource
     *
     * @return Interpreter
     */
    public function create(DataSourceInterface $dataSource)
    {
        return new Interpreter($this->extensionContainer, $dataSource, $this->caseSensitive);
    }
}
