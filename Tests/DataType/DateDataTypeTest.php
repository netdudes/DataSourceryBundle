<?php
namespace Netdudes\DataSourceryBundle\Tests\DataType;

use Netdudes\DataSourceryBundle\DataType\DateDataType;
use Netdudes\DataSourceryBundle\Query\FilterCondition;
use PHPUnit\Framework\TestCase;

class DateDataTypeTest extends TestCase
{
    /**
     * @var DateDataType
     */
    private $dataType;

    public function testExpectedAvailableFilterMethods()
    {
        $expectedAvailableMethods = [
            FilterCondition::METHOD_DATETIME_GT,
            FilterCondition::METHOD_DATETIME_GTE,
            FilterCondition::METHOD_DATETIME_EQ,
            FilterCondition::METHOD_DATETIME_LTE,
            FilterCondition::METHOD_DATETIME_LT,
            FilterCondition::METHOD_DATETIME_NEQ,
        ];

        $this->assertSame($expectedAvailableMethods, $this->dataType->getAvailableFilterMethods());
    }

    public function testExpectedDefaultFilterMethod()
    {
        $expectedDefaultMethod = FilterCondition::METHOD_DATETIME_EQ;

        $this->assertSame($expectedDefaultMethod, $this->dataType->getDefaultFilterMethod());
    }

    protected function setUp()
    {
        $this->dataType = new DateDataType();
    }
}
