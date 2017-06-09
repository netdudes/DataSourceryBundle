<?php
namespace Netdudes\DataSourceryBundle\Tests\DataType;

use Netdudes\DataSourceryBundle\DataType\SearchTextDataType;
use Netdudes\DataSourceryBundle\Query\FilterCondition;
use PHPUnit\Framework\TestCase;

class SearchTextDataTypeTest extends TestCase
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
