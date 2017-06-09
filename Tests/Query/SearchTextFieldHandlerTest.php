<?php
namespace Netdudes\DataSourceryBundle\Tests\Query;

use Netdudes\DataSourceryBundle\DataSource\Configuration\Field;
use Netdudes\DataSourceryBundle\DataType\SearchTextDataType;
use Netdudes\DataSourceryBundle\DataType\StringDataType;
use Netdudes\DataSourceryBundle\Query\Filter;
use Netdudes\DataSourceryBundle\Query\FilterCondition;
use Netdudes\DataSourceryBundle\Query\SearchTextFieldHandler;
use Netdudes\DataSourceryBundle\Query\SearchTextFilterConditionTransformer;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class SearchTextFieldHandlerTest extends TestCase
{
    public function testHandlerDoesNotTransformNonSearchTextFields()
    {
        $fieldName = 'fieldName';

        $field = $this->prophesize(Field::class);
        $field->getUniqueName()->willReturn($fieldName);
        $field->getDataType()->willReturn(new StringDataType());
        $dataSourceFields = [$field->reveal()];

        $filterCondition = $this->prophesize(FilterCondition::class);
        $filterCondition->getFieldName()->willReturn($fieldName);
        $filter = new Filter([$filterCondition->reveal()]);

        $transformer = $this->prophesize(SearchTextFilterConditionTransformer::class);
        $transformer->transform(Argument::any(), Argument::any())->shouldNotBeCalled();

        $handler = new SearchTextFieldHandler($transformer->reveal());
        $handler->handle($filter, $dataSourceFields);

        $this->assertInstanceOf(Filter::class, $filter);
        $this->assertCount(1, $filter);
        $this->assertSame($filterCondition->reveal(), $filter[0]);
    }

    public function testHandlerTransformsSearchTextFields()
    {
        $fieldName = 'fieldName';

        $field = $this->prophesize(Field::class);
        $field->getUniqueName()->willReturn($fieldName);
        $field->getDataType()->willReturn(new SearchTextDataType());
        $dataSourceFields = [$field->reveal()];

        $filterCondition = $this->prophesize(FilterCondition::class);
        $filterCondition->getFieldName()->willReturn($fieldName);
        $filter = new Filter([$filterCondition->reveal()]);

        $transformedFilterCondition = $this->prophesize(FilterCondition::class);

        $transformer = $this->prophesize(SearchTextFilterConditionTransformer::class);
        $transformer->transform($filterCondition, $dataSourceFields)->shouldBeCalled()->willReturn($transformedFilterCondition);

        $handler = new SearchTextFieldHandler($transformer->reveal());
        $handler->handle($filter, $dataSourceFields);

        $this->assertInstanceOf(Filter::class, $filter);
        $this->assertCount(1, $filter);
        $this->assertSame($transformedFilterCondition->reveal(), $filter[0]);
    }

    public function testHandlerTransformsSearchTextFieldsForNestedFilterConditions()
    {
        $fieldName = 'fieldName';

        $field = $this->prophesize(Field::class);
        $field->getUniqueName()->willReturn($fieldName);
        $field->getDataType()->willReturn(new SearchTextDataType());
        $dataSourceFields = [$field->reveal()];

        $filterCondition = $this->prophesize(FilterCondition::class);
        $filterCondition->getFieldName()->willReturn($fieldName);
        $subFilter = new Filter([$filterCondition->reveal()]);
        $filter = new Filter([$subFilter]);

        $transformedFilterCondition = $this->prophesize(FilterCondition::class);

        $transformer = $this->prophesize(SearchTextFilterConditionTransformer::class);
        $transformer->transform($filterCondition, $dataSourceFields)->shouldBeCalled()->willReturn($transformedFilterCondition);

        $handler = new SearchTextFieldHandler($transformer->reveal());
        $handler->handle($filter, $dataSourceFields);

        $this->assertInstanceOf(Filter::class, $filter);
        $this->assertCount(1, $filter);
        $this->assertSame($subFilter, $filter[0]);
        $this->assertCount(1, $subFilter);
        $this->assertSame($transformedFilterCondition->reveal(), $subFilter[0]);
    }
}
