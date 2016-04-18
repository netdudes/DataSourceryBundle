<?php

namespace Netdudes\DataSourceryBundle\UQL;

use Netdudes\DataSourceryBundle\DataSource\DataSourceInterface;
use Netdudes\DataSourceryBundle\Extension\UqlExtensionContainer;
use Netdudes\DataSourceryBundle\Extension\UqlFunctionCaller;
use Netdudes\DataSourceryBundle\Query\FilterConditionFactory;

class InterpreterFactory
{
    /**
     * @var UqlFunctionCaller
     */
    private $uqlFunctionCaller;

    /**
     * @var FilterConditionFactory
     */
    private $filterConditionFactory;

    /**
     * @var bool
     */
    private $caseSensitive;

    /**
     * @param UqlFunctionCaller      $uqlFunctionCaller
     * @param FilterConditionFactory $filterConditionFactory
     * @param bool                   $caseSensitive
     */
    public function __construct(
        UqlFunctionCaller $uqlFunctionCaller,
        FilterConditionFactory $filterConditionFactory,
        $caseSensitive = true
    ) {
        $this->uqlFunctionCaller = $uqlFunctionCaller;
        $this->caseSensitive = $caseSensitive;
        $this->filterConditionFactory = $filterConditionFactory;
    }

    /**
     * @param DataSourceInterface $dataSource
     *
     * @return Interpreter
     */
    public function create(DataSourceInterface $dataSource)
    {
        return new Interpreter($this->uqlFunctionCaller, $dataSource, $this->filterConditionFactory, $this->caseSensitive);
    }
}
