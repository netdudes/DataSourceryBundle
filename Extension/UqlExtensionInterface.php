<?php

namespace Netdudes\DataSourceryBundle\Extension;

interface UqlExtensionInterface
{
    /**
     * An array of functions that will be available to use in UQL
     *
     * @return UqlFunction[]
     */
    public function getFunctions();
}
