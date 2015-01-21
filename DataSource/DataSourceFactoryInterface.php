<?php
namespace Netdudes\DataSourceryBundle\DataSource;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;

interface DataSourceFactoryInterface
{
    public function create($entityClass, array $fields, array $transformers, EventDispatcherInterface $eventDispatcher);
}
