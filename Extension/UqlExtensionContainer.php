<?php

namespace Netdudes\DataSourceryBundle\Extension;

use Netdudes\DataSourceryBundle\Extension\Exception\FunctionNotFoundException;
use Netdudes\DataSourceryBundle\Extension\Exception\InvalidExtensionTypeException;

class UqlExtensionContainer
{
    /**
     * @var UqlExtensionInterface[]
     */
    private $extensions = [];

    /**
     * @var UqlFunctionInterface[]
     */
    private $functions = [];

    /**
     * @return UqlExtensionInterface[]
     */
    public function getExtensions()
    {
        return $this->extensions;
    }

    /**
     * @param string $name
     *
     * @return UqlFunctionInterface
     * @throws FunctionNotFoundException
     */
    public function getFunction($name)
    {
        if (!(isset($this->getFunctions()[$name]))) {
            throw new FunctionNotFoundException("Could not find UQL function $name");
        }

        return $this->getFunctions()[$name];
    }

    /**
     * @return UqlFunctionInterface[]
     */
    public function getFunctions()
    {
        return $this->functions;
    }

    /**
     * Adds an extension to the container. This function is called during the compiler pass.
     *
     * @param UqlExtensionInterface $extension
     *
     * @throws InvalidExtensionTypeException
     */
    public function addExtension(UqlExtensionInterface $extension)
    {
        $this->extensions[] = $extension;

        foreach ($extension->getFunctions() as $function) {
            if (!($function instanceof UqlFunctionInterface)) {
                throw new InvalidExtensionTypeException("Function extensions must implement the UqlFunctionInterface");
            }
            $this->functions[$function->getName()] = $function;
        }
    }
}
