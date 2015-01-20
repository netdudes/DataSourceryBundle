<?php

namespace Netdudes\DataSourceryBundle\Extension;

abstract class AbstractTableBundleExtension implements TableBundleExtensionInterface
{
    abstract public function getName();

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [];
    }
}
