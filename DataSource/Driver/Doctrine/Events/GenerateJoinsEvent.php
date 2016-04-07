<?php
namespace Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\Events;

use Doctrine\ORM\Query\Expr\Join;
use Symfony\Component\EventDispatcher\Event;

class GenerateJoinsEvent extends Event
{
    /**
     * @var Join[]
     */
    public $joins;

    public $fromAlias;

    public function __construct($fromAlias, array $joins)
    {
        $this->fromAlias = $fromAlias;
        $this->joins = $joins;
    }
}
