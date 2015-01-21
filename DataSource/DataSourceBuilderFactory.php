<?php
namespace Netdudes\DataSourceryBundle\DataSource;

use Netdudes\DataSourceryBundle\DataSource\Driver\DriverInterface;
use Netdudes\DataSourceryBundle\DataSource\Util\ChoicesBuilder;
use Symfony\Component\EventDispatcher\EventDispatcher;

class DataSourceBuilderFactory
{

    /**
     * @var ChoicesBuilder
     */
    private $choicesBuilder;

    public function __construct(ChoicesBuilder $choicesBuilder)
    {
        $this->choicesBuilder = $choicesBuilder;
    }

    public function create($entityClass, DataSourceFactoryInterface $dataSourceFactory)
    {
        $eventDispatcher = new EventDispatcher();
        return new DataSourceBuilder($entityClass, $eventDispatcher, $dataSourceFactory, $this->choicesBuilder);
    }
}
