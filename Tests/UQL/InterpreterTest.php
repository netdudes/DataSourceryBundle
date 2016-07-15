<?php
namespace Netdudes\DataSourceryBundle\Tests\UQL;

use Netdudes\DataSourceryBundle\DataSource\Configuration\Field;
use Netdudes\DataSourceryBundle\DataSource\DataSourceInterface;
use Netdudes\DataSourceryBundle\DataType\NumberDataType;
use Netdudes\DataSourceryBundle\DataType\StringDataType;
use Netdudes\DataSourceryBundle\Extension\ContextFactory;
use Netdudes\DataSourceryBundle\Extension\UqlExtensionContainer;
use Netdudes\DataSourceryBundle\Query\Filter;
use Netdudes\DataSourceryBundle\Query\FilterCondition;
use Netdudes\DataSourceryBundle\Query\FilterConditionFactory;
use Netdudes\DataSourceryBundle\UQL\AST\ASTAssertion;
use Netdudes\DataSourceryBundle\UQL\AST\ASTGroup;
use Netdudes\DataSourceryBundle\UQL\InterpreterFactory;

class InterpreterTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContextFactory
     */
    private $contextFactoryProphecy;

    /**
     * @var UqlExtensionContainer
     */
    private $extensionContainerProphecy;

    /**
     * Test the filter construction against a typical complex multilevel situation.
     */
    public function testBuildFilter()
    {
        $astSubtree = new ASTGroup(
            'T_LOGIC_AND',
            [
                new ASTGroup(
                    'T_LOGIC_OR',
                    [
                        new ASTAssertion(
                            'test_dse_1',
                            'T_OP_EQ',
                            'value1'
                        ),
                        new ASTAssertion(
                            'test_dse_2',
                            'T_OP_LT',
                            'value2'
                        ),
                    ]
                ),
                new ASTAssertion(
                    'test_dse_3',
                    'T_OP_NEQ',
                    'value3'
                ),
            ]
        );

        $filterDefinition1 = new Filter();
        $filterDefinition1->setConditionType('OR');
        $filter1 = new FilterCondition('test_dse_1', FilterCondition::METHOD_NUMERIC_EQ, 'value1', 'value1');
        $filter2 = new FilterCondition('test_dse_2', FilterCondition::METHOD_NUMERIC_LT, 'value2', 'value2');
        $filterDefinition1[] = $filter1;
        $filterDefinition1[] = $filter2;
        $filter3 = new FilterCondition('test_dse_3', FilterCondition::METHOD_NUMERIC_NEQ, 'value3', 'value3');
        $filterDefinition2 = new Filter();
        $filterDefinition2->setConditionType('AND');
        $filterDefinition2[] = $filterDefinition1;
        $filterDefinition2[] = $filter3;

        $expectedFilter = $filterDefinition2;

        $dataSourceElements = [
            new Field('test_dse_1', '', '', new NumberDataType()),
            new Field('test_dse_2', '', '', new NumberDataType()),
            new Field('test_dse_3', '', '', new NumberDataType()),
        ];

        $dataSourceProphecy = $this->prophesize(DataSourceInterface::class);
        $dataSourceProphecy->getFields()->willReturn($dataSourceElements);

        $interpreterFactory = new InterpreterFactory($this->extensionContainerProphecy->reveal(), new FilterConditionFactory(), $this->contextFactoryProphecy->reveal());
        $interpreter = $interpreterFactory->create($dataSourceProphecy->reveal());

        $actualFilter = $interpreter->buildFilter($astSubtree);
        $this->assertEquals($expectedFilter, $actualFilter);
    }

    public function testTranslateOperator()
    {
        // Manually set the data source element, mimic what the data source would do
        $dataSourceElement = new Field(
            'test_data_source_element_name',
            '',
            '',
            new StringDataType()
        );

        $dataSourceProphecy = $this->prophesize(DataSourceInterface::class);
        $dataSourceProphecy->getFields()->willReturn([$dataSourceElement]);

        $interpreterFactory = new InterpreterFactory($this->extensionContainerProphecy->reveal(), new FilterConditionFactory(), $this->contextFactoryProphecy->reveal());
        $interpreter = $interpreterFactory->create($dataSourceProphecy->reveal());

        // LIKE is valid for String type, should return LIKE
        $this->assertEquals(FilterCondition::METHOD_STRING_LIKE, $interpreter->translateOperator('T_OP_LIKE', $dataSourceElement), 'Failed to translate T_OP_LIKE into STRING_LIKE for type String');

        // EQ is valid for String, and should choose STRING_EQ as it's the default for the type
        $this->assertEquals(FilterCondition::METHOD_STRING_EQ, $interpreter->translateOperator('T_OP_EQ', $dataSourceElement), 'Failed to translate T_OP_EQ into STRING_EQ for type String');
    }

    protected function setUp()
    {
        $this->extensionContainerProphecy = $this->prophesize(UqlExtensionContainer::class);
        $this->contextFactoryProphecy = $this->prophesize(ContextFactory::class);
    }
}
