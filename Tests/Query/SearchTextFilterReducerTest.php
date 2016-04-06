<?php
namespace Netdudes\DataSourceryBundle\Tests\Query;

use Netdudes\DataSourceryBundle\DataSource\Configuration\Field;
use Netdudes\DataSourceryBundle\DataType\BooleanDataType;
use Netdudes\DataSourceryBundle\DataType\DataTypeInterface;
use Netdudes\DataSourceryBundle\DataType\DateDataType;
use Netdudes\DataSourceryBundle\DataType\EntityDataType;
use Netdudes\DataSourceryBundle\DataType\NumberDataType;
use Netdudes\DataSourceryBundle\DataType\PercentDataType;
use Netdudes\DataSourceryBundle\DataType\StringDataType;
use Netdudes\DataSourceryBundle\Query\Filter;
use Netdudes\DataSourceryBundle\Query\FilterCondition;
use Netdudes\DataSourceryBundle\Query\SearchTextFilterReducer;

class SearchTextFilterReducerTest extends \PHPUnit_Framework_TestCase
{
    public function testReducingDoesNotAffectFilterWithoutSearchTextFilterCondition()
    {
        $fields = [
            $this->buildField('Field1', new NumberDataType(), 'field1'),
            $this->buildField('Field2', new StringDataType(), 'field2'),
            $this->buildField('Field3', new DateDataType(), 'field3'),
            $this->buildField('Field4', new BooleanDataType(), 'field4'),
        ];

        $filterToReduce = $this->createFilter(Filter::CONDITION_TYPE_OR, [
            $this->createFilterCondition('column_string', FilterCondition::METHOD_STRING_LIKE, 'test', false),
            $this->createFilterCondition('column_datetime', FilterCondition::METHOD_DATETIME_EQ, 'now', false),
            $this->createFilterCondition('column_numeric', FilterCondition::METHOD_NUMERIC_NEQ, 123, false),
            $this->createFilter(Filter::CONDITION_TYPE_AND, [
                $this->createFilterCondition('column_bool', FilterCondition::METHOD_BOOLEAN, true, false),
                $this->createFilterCondition('column_set', FilterCondition::METHOD_IN, [1, 2, 3], false),
            ]),
        ]);

        $searchTextFilterReducer = new SearchTextFilterReducer($fields);
        $reducedFilter = $searchTextFilterReducer->reduceToFilterCondition($filterToReduce);

        $this->assertEquals($filterToReduce->toUQL(), $reducedFilter->toUQL());
    }

    public function testReducesSearchTextFilterWithFilterConditions()
    {
        $fields = [
            $this->buildField('Field1', new StringDataType(), 'field1'),
            $this->buildField('Field2', new StringDataType(), 'field2'),
            $this->buildField('Field3', new StringDataType(), 'field3'),
        ];

        $filterToReduce = $this->createFilter(Filter::CONDITION_TYPE_OR, [
            $this->createFilterCondition('column_datetime', FilterCondition::METHOD_DATETIME_EQ, 'now', false),
            $this->createFilterCondition('column_numeric', FilterCondition::METHOD_NUMERIC_NEQ, 123, false),
            $this->createFilter(Filter::CONDITION_TYPE_AND, [
                $this->createFilterCondition('column_bool', FilterCondition::METHOD_BOOLEAN, true, false),
                $this->createFilterCondition('column_set', FilterCondition::METHOD_IN, [1, 2, 3], false),
            ]),
            $this->createFilterCondition('column_search_text', FilterCondition::METHOD_STRING_EQ, 'test', true),
        ]);

        $searchTextFilterReducer = new SearchTextFilterReducer($fields);
        $reducedFilter = $searchTextFilterReducer->reduceToFilterCondition($filterToReduce);

        $this->assertEquals(count($filterToReduce), count($reducedFilter));
        $this->assertEquals($filterToReduce[0]->toUQL(), $reducedFilter[0]->toUQL());
        $this->assertEquals($filterToReduce[1]->toUQL(), $reducedFilter[1]->toUQL());
        $this->assertEquals($filterToReduce[2]->toUQL(), $reducedFilter[2]->toUQL());
        $this->assertEquals('column_search_text = "test"', $filterToReduce[3]->toUQL());
        $this->assertEquals('Field1 = "test" or Field2 = "test" or Field3 = "test"', $reducedFilter[3]->toUQL());
    }

    public function testReducesSearchTextFilterWithFilterConditionsAndUsesWildcardForLikeComparisonMethod()
    {
        $fields = [
            $this->buildField('Field1', new StringDataType(), 'field1'),
            $this->buildField('Field2', new StringDataType(), 'field2'),
            $this->buildField('Field3', new StringDataType(), 'field3'),
        ];

        $filterToReduce = $this->createFilter(Filter::CONDITION_TYPE_OR, [
            $this->createFilterCondition('column_search_text', FilterCondition::METHOD_STRING_LIKE, 'test', true),
        ]);

        $searchTextFilterReducer = new SearchTextFilterReducer($fields);
        $reducedFilter = $searchTextFilterReducer->reduceToFilterCondition($filterToReduce);

        $this->assertEquals('Field1 ~ "%test%" or Field2 ~ "%test%" or Field3 ~ "%test%"', $reducedFilter[0]->toUQL());
    }

    public function testReducesSearchTextFilterWithFilterConditionsButSkipsFieldsThatDoNotSupportGivenMethod()
    {
        $fields = [
            $this->buildField('StringField', new StringDataType(), 'string_field'),
            $this->buildField('BooleanField', new BooleanDataType(), 'boolean_field'),
            $this->buildField('DateField', new DateDataType(), 'date_field'),
            $this->buildField('NumberField', new NumberDataType(), 'number_field'),
            $this->buildField('PercentField', new PercentDataType(), 'percent_field'),
            $this->buildField('EntityField', new EntityDataType(), 'entity_field'),
            $this->buildField('AnotherStringField', new StringDataType(), 'another_string_field'),
        ];

        $filterToReduce = $this->createFilter(Filter::CONDITION_TYPE_OR, [
            $this->createFilterCondition('column_search_text', FilterCondition::METHOD_STRING_EQ, 'test', true),
        ]);

        $searchTextFilterReducer = new SearchTextFilterReducer($fields);
        $reducedFilter = $searchTextFilterReducer->reduceToFilterCondition($filterToReduce);

        $this->assertEquals('StringField = "test" or AnotherStringField = "test"', $reducedFilter[0]->toUQL());
    }

    public function testReducesSearchTextFilterWithFilterConditionsButSkipsFieldsThatDoNotDefineDatabaseFilterQueryField()
    {
        $fields = [
            $this->buildField('FieldWithDbQueryField', new StringDataType(), 'field_with_db_field'),
            $this->buildField('FieldWithoutDbQueryField', new StringDataType(), null),
            $this->buildField('AnotherFieldWithDbQueryField', new StringDataType(), 'another_field_with_db_field'),
        ];

        $filterToReduce = $this->createFilter(Filter::CONDITION_TYPE_OR, [
            $this->createFilterCondition('column_search_text', FilterCondition::METHOD_STRING_EQ, 'test', true),
        ]);

        $searchTextFilterReducer = new SearchTextFilterReducer($fields);
        $reducedFilter = $searchTextFilterReducer->reduceToFilterCondition($filterToReduce);

        $this->assertEquals('FieldWithDbQueryField = "test" or AnotherFieldWithDbQueryField = "test"', $reducedFilter[0]->toUQL());
    }

    public function testReducesSearchTextFilterWithFilterConditionsButSkipsFieldsThatDefineDatabaseSelectAliasAsArray()
    {
        $fields = [
            $this->buildField('FieldWithoutSelectAlias', new StringDataType(), 'field_without_select_alias'),
            $this->buildField('FieldWithSimpleSelectAlias', new StringDataType(), 'field_with_simple_select_alias', 'simple_alias'),
            $this->buildField('FieldWithSelectAliasAsAnArray', new StringDataType(), 'field_with_select_alias_as_an_array', ['alias1', 'alias2']),
        ];

        $filterToReduce = $this->createFilter(Filter::CONDITION_TYPE_OR, [
            $this->createFilterCondition('column_search_text', FilterCondition::METHOD_STRING_EQ, 'test', true),
        ]);

        $searchTextFilterReducer = new SearchTextFilterReducer($fields);
        $reducedFilter = $searchTextFilterReducer->reduceToFilterCondition($filterToReduce);

        $this->assertEquals('FieldWithoutSelectAlias = "test" or FieldWithSimpleSelectAlias = "test"', $reducedFilter[0]->toUQL());
    }

    /**
     * @param string $conditionType
     * @param array  $filterConditions
     *
     * @return Filter
     */
    private function createFilter($conditionType, array $filterConditions = [])
    {
        $filter = new Filter();
        $filter->setConditionType($conditionType);

        foreach ($filterConditions as $filterCondition) {
            $filter[] = $filterCondition;
        }

        return $filter;
    }

    /**
     * @param string $columnIdentifier
     * @param string $method
     * @param mixed  $value
     * @param bool   $isSearchText
     *
     * @return FilterCondition
     */
    private function createFilterCondition($columnIdentifier, $method, $value, $isSearchText)
    {
        $filterCondition = new FilterCondition($columnIdentifier, $method, $value, $value);
        $filterCondition->setIsSearchText($isSearchText);

        return $filterCondition;
    }

    /**
     * @param string               $uniqueName
     * @param DataTypeInterface    $dataType
     * @param string|null          $databaseFilterQueryField
     * @param string|string[]|null $databaseSelectAlias
     *
     * @return Field
     */
    private function buildField(
        $uniqueName,
        DataTypeInterface $dataType,
        $databaseFilterQueryField = null,
        $databaseSelectAlias = null
    ) {
        $field = $this->prophesize(Field::class);
        $field->getUniqueName()->willReturn($uniqueName);
        $field->getDataType()->willReturn($dataType);
        $field->getDatabaseFilterQueryField()->willReturn($databaseFilterQueryField);
        $field->getDatabaseSelectAlias()->willReturn($databaseSelectAlias);

        return $field->reveal();
    }
}
