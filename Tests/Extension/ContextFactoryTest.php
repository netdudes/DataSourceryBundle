<?php
namespace Netdudes\DataSourceryBundle\Tests\Extension;

use Netdudes\DataSourceryBundle\Extension\Context;
use Netdudes\DataSourceryBundle\Extension\ContextFactory;
use PHPUnit\Framework\TestCase;

class ContextFactoryTest extends TestCase
{
    public function testCreateAContext()
    {
        $entityClass = 'entity class';
        $contextFactory = new ContextFactory();
        $context = $contextFactory->create($entityClass);

        $this->assertInstanceOf(Context::class, $context);
    }
}
