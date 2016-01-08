<?php

namespace Netdudes\DataSourceryBundle\Extension;

use Netdudes\DataSourceryBundle\Extension\Type\TableBundleFunctionExtension;

interface TableBundleExtensionInterface
{
    /**
     * An array of functions that will be available to use in UQL
     *
     * @return TableBundleFunctionExtension[]
     */
    public function getFunctions();
}
