<?php
namespace Netdudes\DataSourceryBundle\Query\Exception;

use Exception;

class MethodNotAllowedException extends \Exception
{
    public function __construct($class, $method, $reason = "", \Exception $previous = null)
    {
        $message = "Method '$method' is not allowed for class '$class'" . ($reason ? ': ' . $reason : "");
        parent::__construct($message, 0, $previous);
    }
}
