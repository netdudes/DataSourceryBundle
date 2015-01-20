<?php
namespace Netdudes\DataSourceryBundle\DataSource\Configuration;

use Netdudes\DataSourceryBundle\DataSource\DataSourceBuilder;
use Netdudes\DataSourceryBundle\DataSource\DataSourceBuilderInterface;

interface DataSourceConfigurationInterface
{
    public function getEntityClass();
    public function buildDataSource(DataSourceBuilderInterface $builder);
}
