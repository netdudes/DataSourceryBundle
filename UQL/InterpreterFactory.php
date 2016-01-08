<?php

namespace Netdudes\DataSourceryBundle\UQL;

use Netdudes\DataSourceryBundle\DataSource\DataSourceInterface;
use Netdudes\DataSourceryBundle\Extension\UqlExtensionContainer;

class InterpreterFactory
{
    /**
     * @var UqlExtensionContainer
     */
    private $extensionContainer;

    /**
     * @var bool
     */
    private $caseSensitive;

    /**
     * @param UqlExtensionContainer $extensionContainer
     * @param bool                  $caseSensitive
     */
    public function __construct(UqlExtensionContainer $extensionContainer, $caseSensitive = true)
    {
        $this->extensionContainer = $extensionContainer;
        $this->caseSensitive = $caseSensitive;
    }

    /**
     * Builds an instance of the UQL Interpreter
     *
     * @param DataSourceInterface $dataSource
     *
     * @return Interpreter
     */
    public function create(DataSourceInterface $dataSource)
    {
        return new Interpreter($this->extensionContainer, $dataSource, $this->caseSensitive);
    }
}
