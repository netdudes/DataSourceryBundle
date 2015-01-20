<?php
namespace Netdudes\DataSourceryBundle\Tests\DataSource\Driver\Doctrine\QueryBuilder;

use Netdudes\DataSourceryBundle\DataSource\Configuration\Entity\QueryBuilderDataSourceFieldsFromConfigurationGenerator;
use Netdudes\DataSourceryBundle\DataSource\Configuration\Field;
use Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\QueryBuilder\RequiredFieldsExtractor;
use Netdudes\DataSourceryBundle\DataType\NumberDataType;
use Netdudes\DataSourceryBundle\Query\Filter;
use Netdudes\DataSourceryBundle\Query\FilterCondition;
use Netdudes\DataSourceryBundle\Query\Query;
use Netdudes\DataSourceryBundle\Query\Sort;
use Netdudes\DataSourceryBundle\Query\SortCondition;

class RequiredFieldsExtractorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Netdudes\DataSourceryBundle\DataSource\Auto\RequiredFieldsExtractor::extractRequiredFields
     */
    public function testExtractRequiredFields()
    {
        $extractor = $this->buildExtractor();
        $query = $this->buildQuery();

        $requiredFields = $extractor->extractRequiredFields($query);

        $this->assertCount(7, $requiredFields);
        $this->assertContains('TEST_FIELD_1_REQUIRED_BY_COMPLEX', $requiredFields);
        $this->assertContains('TEST_FIELD_2_REQUIRED_BY_COMPLEX', $requiredFields);
        $this->assertContains('TEST_FIELD_3_THAT_IS_COMPLEX', $requiredFields);
        $this->assertContains('TEST_FIELD_4_REQUIRED_BY_SELECT', $requiredFields);
        $this->assertContains('TEST_FIELD_5_REQUIRED_BY_TRANSFORMER', $requiredFields);
        $this->assertContains('TEST_FIELD_6_REQUIRED_BY_FILTER', $requiredFields);
        $this->assertContains('TEST_FIELD_7_REQUIRED_BY_SORT', $requiredFields);
        $this->assertNotContains('TEST_FIELD_8_NOT_REQUIRED', $requiredFields);
    }

    /**
     * @return \Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\QueryBuilder\RequiredFieldsExtractor
     */
    protected function buildExtractor()
    {
        $queryBuilderFields = [
            new Field('TEST_FIELD_1_REQUIRED_BY_COMPLEX', 'TEST_FIELD', '', new NumberDataType(), 'testField1'),
            new Field('TEST_FIELD_2_REQUIRED_BY_COMPLEX', 'TEST_FIELD', '', new NumberDataType(), 'testField2'),
            new Field('TEST_FIELD_3_THAT_IS_COMPLEX', 'TEST_FIELD', '', new NumberDataType(), null, [
                'SUB_FIELD_1' => 'TEST_FIELD_1_REQUIRED_BY_COMPLEX',
                'SUB_FIELD_2' => 'TEST_FIELD_2_REQUIRED_BY_COMPLEX',
            ]),
            new Field('TEST_FIELD_4_REQUIRED_BY_SELECT', 'TEST_FIELD', '', new NumberDataType(), 'testField4'),
            new Field('TEST_FIELD_5_REQUIRED_BY_TRANSFORMER', 'TEST_FIELD', '', new NumberDataType(), 'testField5'),
            new Field('TEST_FIELD_6_REQUIRED_BY_FILTER', 'TEST_FIELD', '', new NumberDataType(), 'testField6'),
            new Field('TEST_FIELD_7_REQUIRED_BY_SORT', 'TEST_FIELD', '', new NumberDataType(), 'testField7'),
            new Field('TEST_FIELD_8_NOT_REQUIRED', 'TEST_FIELD', '', new NumberDataType(), 'testField8'),
        ];

        $transformer = $this
            ->getMockBuilder('Netdudes\DataSourceryBundle\Transformers\TransformerInterface')
            ->disableOriginalConstructor()
            ->setMethods(['getRequiredFieldNames', 'transform'])
            ->getMock();
        $transformer->expects($this->any())
            ->method('getRequiredFieldNames')
            ->will($this->returnValue(['TEST_FIELD_5_REQUIRED_BY_TRANSFORMER']));

        return new RequiredFieldsExtractor($queryBuilderFields, [$transformer]);
    }

    /**
     * @return Query
     */
    protected function buildQuery()
    {
        $filter = new Filter([new FilterCondition('TEST_FIELD_6_REQUIRED_BY_FILTER', FilterCondition::METHOD_STRING_EQ, 'TEST_VALUE')]);
        $sort = new Sort([new SortCondition('TEST_FIELD_7_REQUIRED_BY_SORT', null, SortCondition::ASC)]);

        $query = new Query();
        $query->setFilter($filter);
        $query->setSort($sort);
        $query->setSelect(['TEST_FIELD_4_REQUIRED_BY_SELECT', 'TEST_FIELD_3_THAT_IS_COMPLEX']);

        return $query;
    }
}
