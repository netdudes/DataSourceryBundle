<?php

namespace Netdudes\DataSourceryBundle\Extension;

abstract class AbstractTableBundleExtension implements TableBundleExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [];
    }
}
