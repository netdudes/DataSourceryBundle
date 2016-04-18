<?php

namespace Netdudes\DataSourceryBundle\Extension;

use Netdudes\DataSourceryBundle\Extension\Exception\FunctionNotFoundException;

class UqlFunctionCaller
{
    /**
     * @var UqlExtensionContainer
     */
    private $uqlExtensionContainer;

    /**
     * @param UqlExtensionContainer $uqlExtensionContainer
     */
    public function __construct(UqlExtensionContainer $uqlExtensionContainer)
    {
        $this->uqlExtensionContainer = $uqlExtensionContainer;
    }

    /**
     * @param string  $name
     * @param array   $arguments
     * @param Context $context
     *
     * @return mixed
     * @throws Exception\FunctionNotFoundException
     */
    public function callFunction($name, $arguments, Context $context)
    {
        $functions = $this->uqlExtensionContainer->getFunctions();
        if (!isset($functions[$name])) {
            throw new FunctionNotFoundException("Could not find UQL function $name");
        }

        return $functions[$name]->call($arguments, $context);
    }
}
