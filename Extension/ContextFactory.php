<?php

namespace Netdudes\DataSourceryBundle\Extension;

class ContextFactory
{
    /**
     * @param string $entityClass
     *
     * @return Context
     */
    public function create($entityClass)
    {
        return new Context($entityClass);
    }
}
