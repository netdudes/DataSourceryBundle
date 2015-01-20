<?php

namespace Netdudes\DataSourceryBundle\Extension;

use Netdudes\DataSourceryBundle\Extension\Type\TableBundleFunctionExtension;

interface TableBundleExtensionInterface
{
    /**
     * Descriptive name of the extension
     *
     * @return string
     */
    public function getName();

    /**
     * An array of functions that will be available to use in UQL
     *
     * @return TableBundleFunctionExtension[]
     */
    public function getFunctions();
}
