<?php
namespace Netdudes\TableBundle\Tests\UQL\Generator;

use Netdudes\DataSourceryBundle\Query\Filter;
use Netdudes\DataSourceryBundle\Query\Pagination;
use Netdudes\DataSourceryBundle\Query\Query;
use Netdudes\DataSourceryBundle\Query\SortCondition;
use Netdudes\DataSourceryBundle\UQL\Interpreter\InterpreterFactory;
use Netdudes\TableBundle\Query\RequestQueryGenerator;
use Symfony\Component\HttpFoundation\ParameterBag;
use Symfony\Component\HttpFoundation\RequestStack;

class RequestQueryGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Netdudes\DataSourceryBundle\Query\Generator\RequestQueryGenerator::generate
     * @covers Netdudes\DataSourceryBundle\Query\Generator\RequestQueryGenerator::buildSelect
     */
    public function testGenerateWithSelectParameter()
    {
        $select = "FIELD_1,FIELD_3,FIELD_8";
        $expectedResult = ["FIELD_1", "FIELD_3", "FIELD_8"];
        $query = $this
            ->generate(
                [\Netdudes\TableBundle\Query\RequestQueryGenerator::QUERY_PARAMETER_SELECT => $select]
            );

        $this->assertCount(3, $query->getSelect(), "Incorrect count of selects returned by the generator");
        foreach ($expectedResult as $result) {
            $this->assertContains($result, $query->getSelect(), "Select is expected to contain $result");
        }
    }

    /**
     * @covers Netdudes\DataSourceryBundle\Query\Generator\RequestQueryGenerator::generate
     * @covers Netdudes\DataSourceryBundle\Query\Generator\RequestQueryGenerator::buildPagination
     */
    public function testGenerateWithCompletePaginationParameter()
    {
        $pagination = "1337,313373";
        $pagination = $this->generate([\Netdudes\TableBundle\Query\RequestQueryGenerator::QUERY_PARAMETER_PAGINATION => $pagination])->getPagination();
        $this->assertEquals(1337, $pagination->getPage(), "Unexpected page number");
        $this->assertEquals(313373, $pagination->getCount(), "Unexpected count");
    }

    /**
     * @covers Netdudes\DataSourceryBundle\Query\Generator\RequestQueryGenerator::generate
     * @covers Netdudes\DataSourceryBundle\Query\Generator\RequestQueryGenerator::buildPagination
     */
    public function testGenerateWithPartialPaginationParameter()
    {
        $pagination = "12345";
        $pagination = $this->generate([RequestQueryGenerator::QUERY_PARAMETER_PAGINATION => $pagination])->getPagination();
        $this->assertEquals(12345, $pagination->getPage(), "Unexpected page number");
        $this->assertEquals(Pagination::DEFAULT_COUNT, $pagination->getCount(), "Unexpected count");
    }

    /**
     * @covers Netdudes\DataSourceryBundle\Query\Generator\RequestQueryGenerator::generate
     * @covers Netdudes\DataSourceryBundle\Query\Generator\RequestQueryGenerator::buildPagination
     */
    public function testGenerateWithEmptyPaginationParameter()
    {
        $pagination = "";
        $pagination = $this->generate([RequestQueryGenerator::QUERY_PARAMETER_PAGINATION => $pagination])->getPagination();
        $this->assertEquals(0, $pagination->getPage(), "Unexpected page number");
        $this->assertEquals(Pagination::DEFAULT_COUNT, $pagination->getCount(), "Unexpected count");
    }

    /**
     * @covers Netdudes\DataSourceryBundle\Query\Generator\RequestQueryGenerator::generate
     * @covers Netdudes\DataSourceryBundle\Query\Generator\RequestQueryGenerator::buildSort
     */
    public function testGenerateWithMultipleSortParameter()
    {
        $sort = "FIELD_1:ASC,FIELD_2:DESC,FIELD_3";
        $sort = $this->generate([\Netdudes\TableBundle\Query\RequestQueryGenerator::QUERY_PARAMETER_SORT => $sort])->getSort();

        $this->assertCount(3, $sort, "Expected three elements in sort");
    }

    /**
     * @covers Netdudes\DataSourceryBundle\Query\Generator\RequestQueryGenerator::generate
     * @covers Netdudes\DataSourceryBundle\Query\Generator\RequestQueryGenerator::buildSort
     */
    public function testGenerateWithSpecificSortParameter()
    {
        $sort = "FIELD:ASC";
        $sort = $this->generate([\Netdudes\TableBundle\Query\RequestQueryGenerator::QUERY_PARAMETER_SORT => $sort])->getSort();
        $this->assertEquals('FIELD', $sort[0]->getFieldName());
        $this->assertEquals(SortCondition::ASC, $sort[0]->getDirection());

        $sort = "FIELD:DESC";
        $sort = $this->generate([\Netdudes\TableBundle\Query\RequestQueryGenerator::QUERY_PARAMETER_SORT => $sort])->getSort();
        $this->assertEquals('FIELD', $sort[0]->getFieldName());
        $this->assertEquals(SortCondition::DESC, $sort[0]->getDirection());
    }

    /**
     * @covers Netdudes\DataSourceryBundle\Query\Generator\RequestQueryGenerator::generate
     * @covers Netdudes\DataSourceryBundle\Query\Generator\RequestQueryGenerator::buildSort
     */
    public function testGenerateWithLooseSortParameter()
    {
        $sort = "FIELD";
        $sort = $this->generate([RequestQueryGenerator::QUERY_PARAMETER_SORT => $sort])->getSort();
        $this->assertEquals('FIELD', $sort[0]->getFieldName());
        $this->assertEquals(SortCondition::ASC, $sort[0]->getDirection());
    }

    /**
     * @covers Netdudes\DataSourceryBundle\Query\Generator\RequestQueryGenerator::canGenerateQueryFromEnvironment
     */
    public function testCanGenerateQueryFromEnvironment()
    {
        $possibleParameters = [
            \Netdudes\TableBundle\Query\RequestQueryGenerator::QUERY_PARAMETER_UQL,
            \Netdudes\TableBundle\Query\RequestQueryGenerator::QUERY_PARAMETER_SELECT,
            RequestQueryGenerator::QUERY_PARAMETER_PAGINATION,
            \Netdudes\TableBundle\Query\RequestQueryGenerator::QUERY_PARAMETER_SORT,
            RequestQueryGenerator::QUERY_PARAMETER_TEXT_SEARCH,
        ];

        $generator = $this->buildGenerator([], 'TEST_UQL_RESULT');
        $this->assertFalse($generator->canGenerateQueryFromEnvironment(), 'The generator should not be able to generate a query with no fields set in the request');

        foreach ($possibleParameters as $parameter) {
            $query = [$parameter => 'TEST_VALUE'];
            $generator = $this->buildGenerator($query, 'TEST_UQL_RESULT');
            $this->assertTrue($generator->canGenerateQueryFromEnvironment(), 'The generator should be able to generate query if any of the parameters is set');
        }
    }

    /**
     * @param array $urlParameters
     *
     * @return RequestStack
     */
    private function buildRequestStack($urlParameters = [])
    {
        $request = $this->getMockBuilder('Symfony\Component\HttpFoundation\Request')
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $request
            ->expects($this->any())
            ->method('get')
            ->will(
                $this->returnCallback(
                    function ($argument) use ($urlParameters) {
                        if (isset($urlParameters[$argument])) {
                            return $urlParameters[$argument];
                        }

                        return null;
                    }
                )
            );

        $request->query = new ParameterBag($urlParameters);

        $stack = $this->getMockBuilder('Symfony\Component\HttpFoundation\RequestStack')
            ->disableOriginalConstructor()
            ->setMethods(['getCurrentRequest'])
            ->getMock();
        $stack
            ->expects($this->any())
            ->method('getCurrentRequest')
            ->will($this->returnValue($request));

        return $stack;
    }

    /**
     * @param $expectedInterpreterResult
     *
     * @return InterpreterFactory
     */
    private function buildUqlInterpreterFactory($expectedInterpreterResult)
    {
        $interpreter = $this->getMockBuilder('Netdudes\DataSourceryBundle\UQL\Interpreter\Interpreter')
            ->disableOriginalConstructor()
            ->setMethods(['generateFilters'])
            ->getMock();

        $interpreter
            ->expects($this->any())
            ->method('generateFilters')
            ->will($this->returnValue($expectedInterpreterResult));

        $interpreterFactory = $this->getMockBuilder('Netdudes\DataSourceryBundle\UQL\Interpreter\InterpreterFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $interpreterFactory
            ->expects($this->any())
            ->method('create')
            ->will($this->returnValue($interpreter));

        return $interpreterFactory;
    }

    /**
     * @param $urlParameters
     * @param $expectedUqlInterpreterResult
     *
     * @return \Netdudes\TableBundle\Query\RequestQueryGenerator
     */
    private function buildGenerator($urlParameters, $expectedUqlInterpreterResult)
    {
        $stack = $this->buildRequestStack($urlParameters);
        $factory = $this->buildUqlInterpreterFactory($expectedUqlInterpreterResult);

        return new \Netdudes\TableBundle\Query\RequestQueryGenerator($stack, $factory);
    }

    /**
     * @param      $urlParameters
     * @param null $expectedUqlInterpreterResult
     *
     * @return Query
     */
    private function generate($urlParameters, $expectedUqlInterpreterResult = null)
    {
        $dataSource = $this->getMockForAbstractClass('Netdudes\DataSourceryBundle\DataSource\DataSourceInterface', [], "", false, true, true, ['getFields']);
        $dataSource
            ->expects($this->any())
            ->method('getFields')
            ->will($this->returnValue([]));

        return $this->buildGenerator($urlParameters, $expectedUqlInterpreterResult ?: new Filter())->generate($dataSource);
    }
}
