<?php

namespace Netdudes\DataSourceryBundle\Extension;

use Netdudes\DataSourceryBundle\Extension\Exception\FunctionNotFoundException;
use Netdudes\DataSourceryBundle\Extension\Exception\InvalidExtensionTypeException;
use Netdudes\DataSourceryBundle\Extension\Type\TableBundleFunctionExtension;

class TableBundleExtensionContainer
{
    /**
     * @var TableBundleExtensionInterface[]
     */
    private $extensions = [];

    /**
     * @var TableBundleFunctionExtension[]
     */
    private $functions = [];

    /**
     * @return TableBundleExtensionInterface[]
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * Performs a call to a function defined in any of the extensions managed by the container.
     *
     * @param $name
     * @param $arguments
     *
     * @return mixed
     * @throws Exception\FunctionNotFoundException
     */
    public function callFunction($name, $arguments)
    {
        if (!(isset($this->getFunctions()[$name]))) {
            throw new FunctionNotFoundException("Could not find UQL function $name");
        }

        return $this->getFunctions()[$name]->call($arguments);
    }

    /**
     * @return TableBundleFunctionExtension[]
     */
    public function getFunctions()
    {
        return $this->functions;
    }

    /**
     * Adds an extension to the container. This function is called during the compiler pass.
     *
     * @param TableBundleExtensionInterface $extension
     *
     * @throws Exception\InvalidExtensionTypeException
     */
    public function addExtension(TableBundleExtensionInterface $extension)
    {
        $this->extensions[] = $extension;

        foreach ($extension->getFunctions() as $function) {
            if (!($function instanceof TableBundleFunctionExtension)) {
                throw new InvalidExtensionTypeException("Function extensions must be of type TableBundleFunctionExtension");
            }
            $this->functions[$function->getName()] = $function;
        }
    }
}
