<?php

namespace Netdudes\DataSourceryBundle\UQL;

use Netdudes\DataSourceryBundle\DataSource\DataSourceInterface;
use Netdudes\DataSourceryBundle\Extension\ContextFactory;
use Netdudes\DataSourceryBundle\Extension\UqlExtensionContainer;
use Netdudes\DataSourceryBundle\Query\FilterConditionFactory;

class InterpreterFactory
{
    /**
     * @var UqlExtensionContainer
     */
    private $extensionContainer;

    /**
     * @var FilterConditionFactory
     */
    private $filterConditionFactory;

    /**
     * @var ContextFactory
     */
    private $contextFactory;

    /**
     * @var bool
     */
    private $caseSensitive;

    /**
     * @param UqlExtensionContainer  $extensionContainer
     * @param FilterConditionFactory $filterConditionFactory
     * @param ContextFactory         $contextFactory
     * @param bool                   $caseSensitive
     */
    public function __construct(
        UqlExtensionContainer $extensionContainer,
        FilterConditionFactory $filterConditionFactory,
        ContextFactory $contextFactory,
        $caseSensitive = true
    ) {
        $this->extensionContainer = $extensionContainer;
        $this->caseSensitive = $caseSensitive;
        $this->filterConditionFactory = $filterConditionFactory;
        $this->contextFactory = $contextFactory;
    }

    /**
     * @param DataSourceInterface $dataSource
     *
     * @return Interpreter
     */
    public function create(DataSourceInterface $dataSource)
    {
        return new Interpreter($this->extensionContainer, $dataSource, $this->filterConditionFactory, $this->contextFactory, $this->caseSensitive);
    }
}
