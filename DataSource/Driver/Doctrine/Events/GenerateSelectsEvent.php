<?php
namespace Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\Events;

use Doctrine\ORM\Query\Expr\Select;
use Symfony\Component\EventDispatcher\Event;

class GenerateSelectsEvent extends Event
{
    /**
     * @var Select
     */
    public $select;

    /**
     * @var
     */
    public $fromAlias;

    /**
     * @param $select
     * @param $fromAlias
     */
    public function __construct($select, $fromAlias)
    {
        $this->select = $select;
        $this->fromAlias = $fromAlias;
    }
}
