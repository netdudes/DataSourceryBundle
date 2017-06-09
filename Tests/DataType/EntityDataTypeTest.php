<?php
namespace Netdudes\DataSourceryBundle\Tests\DataType;

use Netdudes\DataSourceryBundle\DataType\EntityDataType;
use Netdudes\DataSourceryBundle\Query\FilterCondition;
use PHPUnit\Framework\TestCase;

class EntityDataTypeTest extends TestCase
{
    /**
     * @var EntityDataType
     */
    private $dataType;

    public function testExpectedAvailableFilterMethods()
    {
        $expectedAvailableMethods = [
            FilterCondition::METHOD_NUMERIC_EQ,
            FilterCondition::METHOD_NUMERIC_NEQ,
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
        $this->dataType = new EntityDataType();
    }
}
