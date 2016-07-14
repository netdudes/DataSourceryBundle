<?php

namespace Netdudes\DataSourceryBundle\Extension;

interface UqlFunctionInterface
{
    /**
     * @return UqlExtensionInterface
     */
    public function getInstance();

    /**
     * @return string
     */
    public function getMethod();

    /**
     * @return string
     */
    public function getName();

    /**
     * @param array $arguments
     *
     * @return mixed
     */
    public function call($arguments);
}
