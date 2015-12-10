<?php
namespace Netdudes\DataSourceryBundle\DataSource;

use Netdudes\DataSourceryBundle\DataSource\Configuration\DataSourceConfigurationInterface;
use Netdudes\DataSourceryBundle\DataSource\Driver\DriverInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class DataSourceFactory implements DataSourceFactoryInterface
{
    /**
     * @var DataSourceBuilderFactory
     */
    private $dataSourceBuilderFactory;

    /**
     * @param DriverInterface          $driver
     * @param DataSourceBuilderFactory $dataSourceBuilderFactory
     */
    public function __construct(DriverInterface $driver, DataSourceBuilderFactory $dataSourceBuilderFactory)
    {
        $this->driver = $driver;
        $this->dataSourceBuilderFactory = $dataSourceBuilderFactory;
    }

    /**
     * @param DataSourceConfigurationInterface $configuration
     *
     * @return DataSourceInterface
     */
    public function createFromConfiguration(DataSourceConfigurationInterface $configuration)
    {
        $builder = $this->createBuilder($configuration->getEntityClass());
        $configuration->buildDataSource($builder);

        return $builder->build();
    }

    /**
     * @param                          $entityClass
     * @param                          $fields
     * @param                          $transformers
     * @param EventDispatcherInterface $eventDispatcher
     *
     * @return DataSource
     */
    public function create($entityClass, array $fields, array $transformers, EventDispatcherInterface $eventDispatcher)
    {
        return new DataSource($entityClass, $fields, $transformers, $eventDispatcher, $this->driver);
    }

    /**
     * @param $entityClass
     *
     * @return DataSourceBuilder
     */
    public function createBuilder($entityClass)
    {
        return $this->dataSourceBuilderFactory->create($entityClass, $this);
    }
}
