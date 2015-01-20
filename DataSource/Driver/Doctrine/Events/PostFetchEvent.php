<?php
namespace Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\Events;

use Netdudes\DataSourceryBundle\DataSource\DataSourceInterface;
use Symfony\Component\EventDispatcher\Event;

class PostFetchEvent extends Event
{
    public $data;

    /**
     * @var DataSourceInterface
     */
    public $dataSource;

    public function __construct(DataSourceInterface $dataSource, $rows)
    {
        $this->data = $rows;
        $this->dataSource = $dataSource;
    }
}
