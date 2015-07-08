<?php
namespace Netdudes\DataSourceryBundle\Tests\Extension;

use Netdudes\DataSourceryBundle\Extension\BuiltInFunctionsExtension;

class BuiltInFunctionsExtensionTest extends \PHPUnit_Framework_TestCase
{

    public function testToday()
    {
        $now = new \DateTime("2012-06-03T22:22:22+0200");

        $context = $this->prophesize('Symfony\Component\Security\Core\SecurityContextInterface');
        $extension = new BuiltInFunctionsExtension($context->reveal());

        $todayResult = $extension->today(null, $now);
        $this->assertSame("2012-06-03T00:00:00+0200", $todayResult, 'The today function result did not produce the expected result');

        $todayResult = $extension->today(-5, $now);
        $this->assertSame("2012-05-29T00:00:00+0200", $todayResult, 'The today function result did not produce the expected result');

        $todayResult = $extension->today(10, $now);
        $this->assertSame("2012-06-13T00:00:00+0200", $todayResult, 'The today function result did not produce the expected result');
    }

    /**
     * This is just used to manually test the function
     */
    public function testNow()
    {
        $now = new \DateTime("2012-06-03 22:22:22+0200");

        $context = $this->prophesize('Symfony\Component\Security\Core\SecurityContextInterface');
        $extension = new BuiltInFunctionsExtension($context->reveal());

        $todayResult = $extension->now(null, $now);
        $this->assertSame("2012-06-03T22:22:22+0200", $todayResult, 'The today function result did not produce the expected  with no offset');

        $offset = "+5 days";
        $todayResult = $extension->now($offset, $now);
        $this->assertSame("2012-06-08T22:22:22+0200", $todayResult, 'The today function result did not produce the expected result with ofset ' . $offset);

        $offset = "-3 days";
        $todayResult = $extension->now($offset, $now);
        $this->assertSame("2012-05-31T22:22:22+0200", $todayResult, 'The today function result did not produce the expected result with ofset ' . $offset);

        $offset = "-6 days - 3 minutes";
        $todayResult = $extension->now($offset, $now);
        $this->assertSame("2012-05-28T22:19:22+0200", $todayResult, 'The today function result did not produce the expected result with ofset ' . $offset);

        $offset = "-30 minutes";
        $todayResult = $extension->now($offset, $now);
        $this->assertSame("2012-06-03T21:52:22+0200", $todayResult, 'The today function result did not produce the expected result with ofset ' . $offset);
    }

}
