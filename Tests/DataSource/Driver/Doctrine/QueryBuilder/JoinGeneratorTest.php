<?php
namespace Netdudes\DataSourceryBundle\Tests\DataSource\Driver\Doctrine\QueryBuilder;

use Netdudes\DataSourceryBundle\DataSource\Configuration\Entity\QueryBuilderDataSourceFieldsFromConfigurationGenerator;
use Netdudes\DataSourceryBundle\DataSource\Configuration\Field;
use Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\QueryBuilder\JoinGenerator;
use Netdudes\DataSourceryBundle\DataType\NumberDataType;
use Netdudes\DataSourceryBundle\Query\Query;

class JoinGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Netdudes\DataSourceryBundleDataSource\Auto\JoinGenerator::generate
     */
    public function testGenerate()
    {
        $generator = $this->buildJoinGenerator();

        $dummyQuery = new Query();

        $joins = $generator->generate($dummyQuery);

        $this->assertCount(5, $joins, "Incorrect number of joins returned by the generator.");

        // List of simple (ENTITY.RELATION) join strings, indexed by the automatically generated alias
        $joinStringsByAlias = [];
        foreach ($joins as $join) {
            $joinStringsByAlias[$join->getAlias()] = $join->getJoin();
        }

        // First-level joins expected
        $this->assertContains('TEST_FROM_ALIAS.testRelation1', $joinStringsByAlias, "A join to testRelation1 is expected");
        $this->assertContains('TEST_FROM_ALIAS.testRelation2', $joinStringsByAlias, "A join to testRelation2 is expected");

        $firstRelationAlias = array_search('TEST_FROM_ALIAS.testRelation1', $joinStringsByAlias, true);
        $secondRelationAlias = array_search('TEST_FROM_ALIAS.testRelation2', $joinStringsByAlias, true);

        // Second-level joins expected
        $this->assertContains("$firstRelationAlias.testRelation3", $joinStringsByAlias, "A join to testRelation3 through testRelation1 is expected");
        $this->assertContains("$secondRelationAlias.testRelation4", $joinStringsByAlias, "A join to testRelation4 through testRelation2 is expected");

        $fourthRelationAlias = array_search("$secondRelationAlias.testRelation4", $joinStringsByAlias, true);

        // Third-level joins expected
        $this->assertContains("$fourthRelationAlias.testRelation5", $joinStringsByAlias, "A join to testRelation5 through testRelation4 through testRelation2 is expected");
    }

    /**
     * @return JoinGenerator
     */
    private function buildJoinGenerator()
    {
        $queryBuilderDataSourceFields = [
            new Field('TEST_FIELD_1', 'TEST_FIELD', '', new NumberDataType(), 'testField1'),
            new Field('TEST_FIELD_2', 'TEST_FIELD', '', new NumberDataType(), 'testRelation1.testField2'),
            new Field('TEST_FIELD_3', 'TEST_FIELD', '', new NumberDataType(), 'testRelation2.testField3'),
            new Field('TEST_FIELD_4', 'TEST_FIELD', '', new NumberDataType(), 'testRelation1.testRelation3.testField4'),
            new Field('TEST_FIELD_5', 'TEST_FIELD', '', new NumberDataType(), 'testRelation2.testRelation4.testRelation5.testField5'),
            new Field('TEST_FIELD_6', 'TEST_FIELD', '', new NumberDataType(), 'testField6'),
            new Field('TEST_FIELD_7', 'TEST_FIELD', '', new NumberDataType(), 'testRelation6.testField7'),
        ];

        $fromAlias = 'TEST_FROM_ALIAS';
        $requiredFieldsExtractor = $this
            ->getMockBuilder('Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\QueryBuilder\RequiredFieldsExtractor')
            ->disableOriginalConstructor()
            ->setMethods(['extractRequiredFields'])
            ->getMock();
        $requiredFieldsExtractor->expects($this->any())
            ->method('extractRequiredFields')
            ->will($this->returnValue(['TEST_FIELD_1', 'TEST_FIELD_2', 'TEST_FIELD_3', 'TEST_FIELD_4', 'TEST_FIELD_5']));

        return new JoinGenerator($queryBuilderDataSourceFields, $fromAlias, $requiredFieldsExtractor);
    }
}
