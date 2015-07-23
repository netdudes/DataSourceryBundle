<?php
namespace Netdudes\DataSourceryBundle\Util;

class CurrentDateTimeProvider
{
    /**
     * @return \DateTime
     */
    public function get()
    {
        return new \DateTime();
    }
}
