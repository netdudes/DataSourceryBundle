<?php
namespace Netdudes\DataSourceryBundle\Tests\DataType;

use Netdudes\DataSourceryBundle\DataType\StringDataType;
use Netdudes\DataSourceryBundle\Query\FilterCondition;

class StringDataTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var StringDataType
     */
    private $dataType;

    public function testExpectedAvailableFilterMethods()
    {
        $expectedAvailableMethods = [
            FilterCondition::METHOD_STRING_EQ,
            FilterCondition::METHOD_STRING_NEQ,
            FilterCondition::METHOD_STRING_LIKE,
            FilterCondition::METHOD_IN,
        ];

        $this->assertSame($expectedAvailableMethods, $this->dataType->getAvailableFilterMethods());
    }

    public function testExpectedDefaultFilterMethod()
    {
        $expectedDefaultMethod = FilterCondition::METHOD_STRING_EQ;

        $this->assertSame($expectedDefaultMethod, $this->dataType->getDefaultFilterMethod());
    }

    protected function setUp()
    {
        $this->dataType = new StringDataType();
    }
}
