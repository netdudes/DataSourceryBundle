<?php
namespace Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\Events;

use Symfony\Component\EventDispatcher\Event;

class GenerateJoinsEvent extends Event
{
    /**
     * @var array
     */
    public $joins;

    public $fromAlias;

    public function __construct($fromAlias, array $joins)
    {
        $this->fromAlias = $fromAlias;
        $this->joins = $joins;
    }
}
