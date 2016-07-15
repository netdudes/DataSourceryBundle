<?php
namespace Netdudes\DataSourceryBundle\Tests\Extension;

use Netdudes\DataSourceryBundle\Extension\Context;
use Netdudes\DataSourceryBundle\Extension\ContextFactory;

class ContextFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateAContext()
    {
        $entityClass = 'entity class';
        $contextFactory = new ContextFactory();
        $context = $contextFactory->create($entityClass);

        $this->assertInstanceOf(Context::class, $context);
    }
}
