<?php
namespace Netdudes\DataSourceryBundle\Tests\DataType;

use Netdudes\DataSourceryBundle\DataType\EntityExistenceDataType;
use Netdudes\DataSourceryBundle\Query\FilterCondition;

class EntityExistenceDataTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityExistenceDataType
     */
    private $dataType;

    public function testExpectedAvailableFilterMethods()
    {
        $expectedAvailableMethods = [
            FilterCondition::METHOD_NUMERIC_EQ,
            FilterCondition::METHOD_NUMERIC_NEQ,
            FilterCondition::METHOD_IS_NULL
        ];

        $this->assertSame($expectedAvailableMethods, $this->dataType->getAvailableFilterMethods());
    }

    public function testExpectedDefaultFilterMethod()
    {
        $expectedDefaultMethod = FilterCondition::METHOD_IS_NULL;

        $this->assertSame($expectedDefaultMethod, $this->dataType->getDefaultFilterMethod());
    }

    protected function setUp()
    {
        $this->dataType = new EntityExistenceDataType();
    }
}
