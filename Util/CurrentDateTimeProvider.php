<?php
namespace Netdudes\DataSourceryBundle\Util;

class CurrentDateTimeProvider
{
    public function get()
    {
        return new \DateTime();
    }
}