<?php
namespace Netdudes\DataSourceryBundle\Tests\DataSource\Driver\Doctrine\QueryBuilder;

use Doctrine\ORM\Query\Expr\Join;
use Netdudes\DataSourceryBundle\DataSource\Configuration\Field;
use Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\QueryBuilder\JoinGenerator;
use Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\QueryBuilder\RequiredFieldsExtractor;
use Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\QueryBuilder\SelectGenerator;
use Netdudes\DataSourceryBundle\DataType\NumberDataType;
use Netdudes\DataSourceryBundle\Query\Query;
use PHPUnit\Framework\TestCase;

class SelectGeneratorTest extends TestCase
{
    public function testGetSelectFieldMap()
    {
        $generator = $this->buildSelectGenerator();
        $dummyQuery = new Query();

        $map = $generator->getSelectFieldMap($dummyQuery);

        $this->assertCount(3, $map, "Unexpected number of entries in the map");
        $this->assertArrayHasKey('FIELD_1', $map, "FIELD_1_ON_ENTITY is expected to be in the select field map");
        $this->assertArrayHasKey('RELATION_1_FIELD_2', $map, "FIELD_2_ON_RELATION_1 is expected to be in the select field map");
        $this->assertArrayHasKey('RELATION_2_RELATION_3_FIELD_3', $map, "FIELD_3_ON_RELATION_3 is expected to be in the select field map");

        $this->assertEquals('ENTITY_ALIAS.FIELD_1', $map['FIELD_1'], 'Unexpected select path for alias');
        $this->assertEquals('RELATION_1_ALIAS.FIELD_2', $map['RELATION_1_FIELD_2'], 'Unexpected select path for alias');
        $this->assertEquals('RELATION_3_ALIAS.FIELD_3', $map['RELATION_2_RELATION_3_FIELD_3'], 'Unexpected select path for alias');
    }

    public function testGenerate()
    {
        $generator = $this->buildSelectGenerator();
        $dummyQuery = new Query();

        $select = $generator->generate($dummyQuery);

        $this->assertEquals(3, $select->count(), "The SELECT is expected to have three statements");
        $this->assertContains('ENTITY_ALIAS.FIELD_1 FIELD_1', $select->getParts(), "Expected select statement is missing");
        $this->assertContains('RELATION_1_ALIAS.FIELD_2 RELATION_1_FIELD_2', $select->getParts(), "Expected select statement is missing");
        $this->assertContains('RELATION_3_ALIAS.FIELD_3 RELATION_2_RELATION_3_FIELD_3', $select->getParts(), "Expected select statement is missing");
    }

    /**
     * @return RequiredFieldsExtractor
     */
    private function buildRequiredFieldsExtractor()
    {
        $extractor = $this->getMockBuilder('Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\QueryBuilder\RequiredFieldsExtractor')
            ->disableOriginalConstructor()
            ->setMethods(['extractRequiredFields'])
            ->getMock();

        $extractor
            ->expects($this->any())
            ->method('extractRequiredFields')
            ->will($this->returnValue(['FIELD_1_ON_ENTITY', 'FIELD_2_ON_RELATION_1', 'FIELD_3_ON_RELATION_3']));

        return $extractor;
    }

    /**
     * @return JoinGenerator
     */
    private function buildJoinGenerator()
    {
        $joins = [
            'RELATION_1' => new Join(Join::LEFT_JOIN, 'ENTITY_ALIAS.RELATION_1', 'RELATION_1_ALIAS'),
            'RELATION_2' => new Join(Join::LEFT_JOIN, 'ENTITY_ALIAS.RELATION_2', 'RELATION_2_ALIAS'),
            'RELATION_2.RELATION_3' => new Join(Join::LEFT_JOIN, 'RELATION_2_ALIAS.RELATION_3', 'RELATION_3_ALIAS'),
        ];

        $generator = $this->getMockBuilder('Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\QueryBuilder\JoinGenerator')
            ->disableOriginalConstructor()
            ->setMethods(['generate'])
            ->getMock();

        $generator
            ->expects($this->any())
            ->method('generate')
            ->will($this->returnValue($joins));

        return $generator;
    }

    /**
     * @return array
     */
    private function buildDataSourceFields()
    {
        return [
            new Field('FIELD_1_ON_ENTITY', 'FIELD', '', new NumberDataType(), 'FIELD_1'),
            new Field('FIELD_2_ON_RELATION_1', 'FIELD', '', new NumberDataType(), 'RELATION_1.FIELD_2'),
            new Field('FIELD_3_ON_RELATION_3', 'FIELD', '', new NumberDataType(), 'RELATION_2.RELATION_3.FIELD_3'),
            new Field('FIELD_4', 'FIELD', '', new NumberDataType(), 'RELATION_2.FIELD_4'),
        ];
    }

    /**
     * @return SelectGenerator
     */
    private function buildSelectGenerator()
    {
        return new \Netdudes\DataSourceryBundle\DataSource\Driver\Doctrine\QueryBuilder\SelectGenerator(
            $this->buildDataSourceFields(),
            'ENTITY_ALIAS',
            $this->buildJoinGenerator(),
            $this->buildRequiredFieldsExtractor()
        );
    }
}
