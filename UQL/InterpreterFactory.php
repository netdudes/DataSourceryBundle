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
     * @param TableBundleExtensionContainer $extensionContainer
     */
    public function __construct(TableBundleExtensionContainer $extensionContainer)
    {
        $this->extensionContainer = $extensionContainer;
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
        return new Interpreter($this->extensionContainer, $dataSource);
    }
}
