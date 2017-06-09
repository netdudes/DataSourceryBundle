<?php
namespace Netdudes\DataSourceryBundle\Tests\DataType;

use Netdudes\DataSourceryBundle\DataType\PercentDataType;
use Netdudes\DataSourceryBundle\Query\FilterCondition;
use PHPUnit\Framework\TestCase;

class PercentDataTypeTest extends TestCase
{
    /**
     * @var PercentDataType
     */
    private $dataType;

    public function testExpectedDefaultFilterMethod()
    {
        $expectedDefaultMethod = FilterCondition::METHOD_NUMERIC_EQ;

        $this->assertSame($expectedDefaultMethod, $this->dataType->getDefaultFilterMethod());
    }

    public function testExpectedAvailableFilterMethods()
    {
        $expectedAvailableMethods = [
            FilterCondition::METHOD_NUMERIC_GT,
            FilterCondition::METHOD_NUMERIC_GTE,
            FilterCondition::METHOD_NUMERIC_EQ,
            FilterCondition::METHOD_NUMERIC_LTE,
            FilterCondition::METHOD_NUMERIC_LT,
            FilterCondition::METHOD_NUMERIC_NEQ,
        ];

        $this->assertSame($expectedAvailableMethods, $this->dataType->getAvailableFilterMethods());
    }

    protected function setUp()
    {
        $this->dataType = new PercentDataType();
    }
}
