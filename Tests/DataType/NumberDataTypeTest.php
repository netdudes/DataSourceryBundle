<?php
namespace Netdudes\DataSourceryBundle\Tests\DataType;

use Netdudes\DataSourceryBundle\DataType\NumberDataType;
use Netdudes\DataSourceryBundle\Query\FilterCondition;
use PHPUnit\Framework\TestCase;

class NumberDataTypeTest extends TestCase
{
    /**
     * @var NumberDataType
     */
    private $dataType;

    public function testExpectedAvailableFilterMethods()
    {
        $expectedAvailableMethods = [
            FilterCondition::METHOD_NUMERIC_GT,
            FilterCondition::METHOD_NUMERIC_GTE,
            FilterCondition::METHOD_NUMERIC_EQ,
            FilterCondition::METHOD_NUMERIC_LTE,
            FilterCondition::METHOD_NUMERIC_LT,
            FilterCondition::METHOD_NUMERIC_NEQ,
            FilterCondition::METHOD_IN,
            FilterCondition::METHOD_NIN,
        ];

        $this->assertSame($expectedAvailableMethods, $this->dataType->getAvailableFilterMethods());
    }

    public function testExpectedDefaultFilterMethod()
    {
        $expectedDefaultMethod = FilterCondition::METHOD_NUMERIC_EQ;

        $this->assertSame($expectedDefaultMethod, $this->dataType->getDefaultFilterMethod());
    }

    protected function setUp()
    {
        $this->dataType = new NumberDataType();
    }
}
