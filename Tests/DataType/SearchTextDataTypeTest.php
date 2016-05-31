<?php
namespace Netdudes\DataSourceryBundle\Tests\DataType;

use Netdudes\DataSourceryBundle\DataType\SearchTextDataType;
use Netdudes\DataSourceryBundle\Query\FilterCondition;

class SearchTextDataTypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SearchTextDataType
     */
    private $dataType;

    public function testExpectedAvailableFilterMethods()
    {
        $expectedAvailableMethods = [
            FilterCondition::METHOD_STRING_EQ,
            FilterCondition::METHOD_STRING_LIKE,
        ];

        $this->assertSame($expectedAvailableMethods, $this->dataType->getAvailableFilterMethods());
    }

    public function testExpectedDefaultFilterMethod()
    {
        $expectedDefaultMethod = FilterCondition::METHOD_STRING_LIKE;

        $this->assertSame($expectedDefaultMethod, $this->dataType->getDefaultFilterMethod());
    }

    protected function setUp()
    {
        $this->dataType = new SearchTextDataType();
    }
}
