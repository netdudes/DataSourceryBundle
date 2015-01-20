<?php
namespace Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\QueryBuilder;

use Doctrine\ORM\EntityManager;
use Netdudes\DataSourceryBundle\DataSource\DataSourceInterface;

class BuilderFactory
{
    /**
     * @var EntityManager
     */
    private $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function create(DataSourceInterface $dataSource)
    {
        return new Builder($dataSource, $this->entityManager);
    }
}
