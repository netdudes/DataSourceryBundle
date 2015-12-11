<?php
namespace Netdudes\DataSourceryBundle\Tests\Extension;

use Netdudes\DataSourceryBundle\Extension\BuiltInFunctionsExtension;
use Netdudes\DataSourceryBundle\Util\CurrentDateTimeProvider;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class BuiltInFunctionsExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * This is just used to manually test the function
     */
    public function testNow()
    {
        $extension = $this->getExtension();

        $todayResult = $extension->now(null);
        $this->assertSame("2012-06-03T22:22:22+0200", $todayResult, 'The today function result did not produce the expected  with no offset');

        $offset = "+5 days";
        $todayResult = $extension->now($offset);
        $this->assertSame("2012-06-08T22:22:22+0200", $todayResult, 'The today function result did not produce the expected result with ofset ' . $offset);

        $offset = "-3 days";
        $todayResult = $extension->now($offset);
        $this->assertSame("2012-05-31T22:22:22+0200", $todayResult, 'The today function result did not produce the expected result with ofset ' . $offset);

        $offset = "-6 days - 3 minutes";
        $todayResult = $extension->now($offset);
        $this->assertSame("2012-05-28T22:19:22+0200", $todayResult, 'The today function result did not produce the expected result with ofset ' . $offset);

        $offset = "-30 minutes";
        $todayResult = $extension->now($offset);
        $this->assertSame("2012-06-03T21:52:22+0200", $todayResult, 'The today function result did not produce the expected result with ofset ' . $offset);
    }

    public function testStartOfDay()
    {
        $extension = $this->getExtension();

        $startOfDayResult = $extension->startOfDay();
        $this->assertSame('2012-06-03T00:00:00+0200', $startOfDayResult, 'The startOfDay function result did not produce the expected result');

        $startOfDayResult = $extension->startOfDay('+2 hours');
        $this->assertSame('2012-06-04T00:00:00+0200', $startOfDayResult, 'The startOfDay function result did not produce the expected result');

        $startOfDayResult = $extension->startOfDay('-5 days');
        $this->assertSame('2012-05-29T00:00:00+0200', $startOfDayResult, 'The startOfDay function result did not produce the expected result');

        $startOfDayResult = $extension->startOfDay('+1 month');
        $this->assertSame('2012-07-03T00:00:00+0200', $startOfDayResult, 'The startOfDay function result did not produce the expected result');

        $startOfDayResult = $extension->startOfDay('15-05-2012');
        $this->assertSame('2012-05-15T00:00:00+0200', $startOfDayResult, 'The startOfDay function result did not produce the expected result');

        $startOfDayResult = $extension->startOfDay('2012-05-15');
        $this->assertSame('2012-05-15T00:00:00+0200', $startOfDayResult, 'The startOfDay function result did not produce the expected result');

        $startOfDayResult = $extension->startOfDay('15.05.2012');
        $this->assertSame('2012-05-15T00:00:00+0200', $startOfDayResult, 'The startOfDay function result did not produce the expected result');
    }

    public function testStartOfWeek()
    {
        $extension = $this->getExtension();

        $startOfWeekResult = $extension->startOfWeek();
        $this->assertSame('2012-05-28T00:00:00+0200', $startOfWeekResult, 'The startOfWeek function result did not produce the expected result');

        $startOfWeekResult = $extension->startOfWeek('+2 hours');
        $this->assertSame('2012-06-04T00:00:00+0200', $startOfWeekResult, 'The startOfWeek function result did not produce the expected result');

        $startOfWeekResult = $extension->startOfWeek('-5 days');
        $this->assertSame('2012-05-28T00:00:00+0200', $startOfWeekResult, 'The startOfWeek function result did not produce the expected result');

        $startOfWeekResult = $extension->startOfWeek('+1 month');
        $this->assertSame('2012-07-02T00:00:00+0200', $startOfWeekResult, 'The startOfWeek function result did not produce the expected result');

        $startOfWeekResult = $extension->startOfWeek('15-05-2012');
        $this->assertSame('2012-05-14T00:00:00+0200', $startOfWeekResult, 'The startOfWeek function result did not produce the expected result');

        $startOfWeekResult = $extension->startOfWeek('2012-05-15');
        $this->assertSame('2012-05-14T00:00:00+0200', $startOfWeekResult, 'The startOfWeek function result did not produce the expected result');

        $startOfWeekResult = $extension->startOfWeek('15.05.2012');
        $this->assertSame('2012-05-14T00:00:00+0200', $startOfWeekResult, 'The startOfWeek function result did not produce the expected result');
    }

    public function testStartOfMonth()
    {
        $extension = $this->getExtension();

        $startOfMonthResult = $extension->startOfMonth();
        $this->assertSame('2012-06-01T00:00:00+0200', $startOfMonthResult, 'The startOfMonth function result did not produce the expected result');

        $startOfMonthResult = $extension->startOfMonth('+2 hours');
        $this->assertSame('2012-06-01T00:00:00+0200', $startOfMonthResult, 'The startOfMonth function result did not produce the expected result');

        $startOfMonthResult = $extension->startOfMonth('-5 days');
        $this->assertSame('2012-05-01T00:00:00+0200', $startOfMonthResult, 'The startOfMonth function result did not produce the expected result');

        $startOfMonthResult = $extension->startOfMonth('+1 month');
        $this->assertSame('2012-07-01T00:00:00+0200', $startOfMonthResult, 'The startOfMonth function result did not produce the expected result');

        $startOfMonthResult = $extension->startOfMonth('15-05-2012');
        $this->assertSame('2012-05-01T00:00:00+0200', $startOfMonthResult, 'The startOfMonth function result did not produce the expected result');

        $startOfMonthResult = $extension->startOfMonth('2012-05-15');
        $this->assertSame('2012-05-01T00:00:00+0200', $startOfMonthResult, 'The startOfMonth function result did not produce the expected result');

        $startOfMonthResult = $extension->startOfMonth('15.05.2012');
        $this->assertSame('2012-05-01T00:00:00+0200', $startOfMonthResult, 'The startOfMonth function result did not produce the expected result');
    }


    /**
     * @return BuiltInFunctionsExtension
     */
    private function getExtension()
    {
        $now = new \DateTime("2012-06-03T22:22:22+0200");

        $tokenStorage = $this->prophesize(TokenStorageInterface::class);
        $provider = $this->prophesize(CurrentDateTimeProvider::class);
        $provider->get()->willReturn($now);
        $extension = new BuiltInFunctionsExtension($tokenStorage->reveal(), $provider->reveal());

        return $extension;
    }
}
