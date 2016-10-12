<?php
namespace Netdudes\DataSourceryBundle\Tests\DataType;

use Netdudes\DataSourceryBundle\DataType\BooleanDataType;
use Netdudes\DataSourceryBundle\Query\FilterCondition;

class BooleanDataTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BooleanDataType
     */
    private $dataType;

    public function testExpectedAvailableFilterMethods()
    {
        $expectedAvailableMethods = [
            FilterCondition::METHOD_BOOLEAN,
            FilterCondition::METHOD_IS_NULL,
            FilterCondition::METHOD_IS_NNULL,
        ];

        $this->assertSame($expectedAvailableMethods, $this->dataType->getAvailableFilterMethods());
    }

    public function testExpectedDefaultFilterMethod()
    {
        $expectedDefaultMethod = FilterCondition::METHOD_BOOLEAN;

        $this->assertSame($expectedDefaultMethod, $this->dataType->getDefaultFilterMethod());
    }

    protected function setUp()
    {
        $this->dataType = new BooleanDataType();
    }
}
