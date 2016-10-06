<?php
namespace Netdudes\DataSourceryBundle\Tests\DataType;

use Netdudes\DataSourceryBundle\DataType\NumberDataType;
use Netdudes\DataSourceryBundle\Query\FilterCondition;

class NumberDataTypeTest extends \PHPUnit_Framework_TestCase
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
            FilterCondition::METHOD_IS_NULL,
            FilterCondition::METHOD_IS_NNULL,
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
