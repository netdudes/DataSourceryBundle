<?php
namespace Netdudes\TableBundle\Tests\UQL;

use Netdudes\DataSourceryBundle\Query\Filter;
use Netdudes\DataSourceryBundle\Query\FilterCondition;
use Netdudes\DataSourceryBundle\Query\Pagination;
use Netdudes\DataSourceryBundle\Query\Sort;
use Netdudes\TableBundle\Query\Query;
use Netdudes\TableBundle\Query\QueryTranslator;

class QueryTranslatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Cache of a filter used for testing
     *
     * @var Filter
     */
    protected $bakedFilter;

    /**
     * @covers Netdudes\DataSourceryBundle\UQL\QueryTranslator::translate
     */
    public function testTranslateUql()
    {
        $translator = $this->getQueryTranslator();
        $query = new Query();
        $query->setUql('TEST UQL');

        $dataSourceQuery = $translator->translate($query);

        $this->assertSame($this->getBakedFilter(), $dataSourceQuery->getFilter(), "The translator is expected to parse the UQL and set the result as the filter");
    }

    /**
     * @covers Netdudes\DataSourceryBundle\UQL\QueryTranslator::translate
     */
    public function testTranslateSort()
    {
        $translator = $this->getQueryTranslator();
        $testSort = new Sort();
        $query = new Query();
        $query->setSort($testSort);

        $dataSourceQuery = $translator->translate($query);

        $this->assertSame($testSort, $dataSourceQuery->getSort(), "The translator is expected to pass through the sort");
    }

    /**
     * @covers Netdudes\DataSourceryBundle\UQL\QueryTranslator::translate
     */
    public function testTranslatePagination()
    {
        $translator = $this->getQueryTranslator();
        $testPagination = new Pagination();
        $query = new Query();
        $query->setPagination($testPagination);

        $dataSourceQuery = $translator->translate($query);

        $this->assertSame($testPagination, $dataSourceQuery->getPagination(), "The translator is expected to pass through the pagination");
    }

    /**
     * @covers Netdudes\DataSourceryBundle\UQL\QueryTranslator::translate
     */
    public function testTranslateSelect()
    {
        $translator = $this->getQueryTranslator();
        $testSelect = ['TEST_FIELD_1', 'TEST_FIELD_2'];
        $query = new Query();
        $query->setSelect($testSelect);

        $dataSourceQuery = $translator->translate($query);

        $this->assertEquals($testSelect, $dataSourceQuery->getSelect(), "The translator is expected to pass through the select");
    }

    /**
     * @covers Netdudes\DataSourceryBundle\UQL\QueryTranslator::translate
     */
    public function testTranslateSearch()
    {
        $translator = $this->getQueryTranslator();
        $testSearch = "TEST SEARCH TERM";
        $query = new Query();
        $query->setSearch($testSearch);

        $dataSourceQuery = $translator->translate($query);

        $this->assertSame($this->getBakedFilter(), $dataSourceQuery->getFilter(), "The translator is expected to parse the full text search and set the result as the filter");
    }

    /**
     * @covers Netdudes\DataSourceryBundle\UQL\QueryTranslator::translate
     */
    public function testTranslateUqlAndSearch()
    {
        $translator = $this->getQueryTranslator();
        $testSearch = "TEST SEARCH TERM";
        $testUql = "TEST UQL";
        $query = new Query();
        $query->setSearch($testSearch);
        $query->setUql($testUql);

        $dataSourceQuery = $translator->translate($query);

        $this->assertCount(2, $dataSourceQuery->getFilter(), "A complex filter with both the results of the UQL and the Search is expected");
        $this->assertSame($this->getBakedFilter(), $dataSourceQuery->getFilter()[0], "The filter is expected to be converted into a filter");
        $this->assertSame($this->getBakedFilter(), $dataSourceQuery->getFilter()[1], "The search is expected to be converted into a filter");
    }

    /**
     * @return QueryTranslator
     */
    private function getQueryTranslator()
    {
        $interpreter = $this->getMockBuilder('Netdudes\DataSourceryBundle\UQL\Interpreter\Interpreter')
            ->disableOriginalConstructor()
            ->setMethods(['generateFilters'])
            ->getMock();

        $interpreter
            ->expects($this->any())
            ->method('generateFilters')
            ->will($this->returnCallback(
                    function ($argument) {
                        if (!$argument) {
                            return new Filter();
                        }

                        return $this->getBakedFilter();
                    }
                )
            );

        $dataSource = $this->getMockForAbstractClass('Netdudes\DataSourceryBundle\DataSource\DataSourceInterface');

        $filterBuilderHelper = $this->getMockBuilder('Netdudes\DataSourceryBundle\Query\Helper\FilterBuilderHelper')
            ->disableOriginalConstructor()
            ->setMethods(['buildFullTextSearchFilter'])
            ->getMock();

        $filterBuilderHelper
            ->expects($this->any())
            ->method('buildFullTextSearchFilter')
            ->will($this->returnCallback(
                    function ($argument) {
                        if (!$argument) {
                            return new Filter();
                        }

                        return $this->getBakedFilter();
                    }
                )
            );

        return new QueryTranslator($interpreter, $dataSource, $filterBuilderHelper);
    }

    /**
     * @return array|Filter
     */
    private function getBakedFilter()
    {
        if (is_null($this->bakedFilter)) {
            $this->bakedFilter = new Filter();
            $this->bakedFilter[] = new FilterCondition('TEST', FilterCondition::METHOD_NUMERIC_EQ, 'TEST');
        }

        return $this->bakedFilter;
    }
}
