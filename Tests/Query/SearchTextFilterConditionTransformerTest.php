<?php
namespace Netdudes\DataSourceryBundle\Tests\Query;

use Netdudes\DataSourceryBundle\DataSource\Configuration\Field;
use Netdudes\DataSourceryBundle\DataType\DataTypeInterface;
use Netdudes\DataSourceryBundle\Query\Filter;
use Netdudes\DataSourceryBundle\Query\FilterCondition;
use Netdudes\DataSourceryBundle\Query\SearchTextFilterConditionTransformer;
use Prophecy\Argument;

class SearchTextFilterConditionTransformerTest extends \PHPUnit_Framework_TestCase
{
    public function testTransformCreatesFilterWithOrMethod()
    {
        $filterCondition = $this->prophesize(FilterCondition::class);
        $transformer = new SearchTextFilterConditionTransformer();
        $result = $transformer->transform($filterCondition->reveal(), []);

        $this->assertEquals(Filter::CONDITION_TYPE_OR, $result->getConditionType());
    }

    public function testTransformWithOneField()
    {
        $searchPhrase = 'search phrase';

        $dataType = $this->prophesize(DataTypeInterface::class);
        $dataType->supports(Argument::any())->willReturn(true);

        $field = $this->prophesize(Field::class);
        $field->getDataType()->willReturn($dataType->reveal());
        $field->getDatabaseFilterQueryField()->willReturn('query_field');
        $field->getDatabaseSelectAlias()->willReturn('alias');
        $field->getUniqueName()->shouldBeCalled();

        $filterCondition = $this->prophesize(FilterCondition::class);
        $filterCondition->getValue()->willReturn($searchPhrase);
        $filterCondition->getMethod()->shouldBeCalled();
        $transformer = new SearchTextFilterConditionTransformer();
        $result = $transformer->transform($filterCondition->reveal(), [$field->reveal()]);

        $this->assertInstanceOf(Filter::class, $result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(FilterCondition::class, $result[0]);
        $this->assertEquals($searchPhrase, $result[0]->getValue());
    }

    public function testTransformWithOneFieldUsingLikeMethod()
    {
        $searchPhrase = 'search phrase';

        $dataType = $this->prophesize(DataTypeInterface::class);
        $dataType->supports(Argument::any())->willReturn(true);

        $field = $this->prophesize(Field::class);
        $field->getDataType()->willReturn($dataType->reveal());
        $field->getDatabaseFilterQueryField()->willReturn('query_field');
        $field->getDatabaseSelectAlias()->willReturn('alias');
        $field->getUniqueName()->shouldBeCalled();

        $filterCondition = $this->prophesize(FilterCondition::class);
        $filterCondition->getValue()->willReturn($searchPhrase);
        $filterCondition->getMethod()->willReturn(FilterCondition::METHOD_STRING_LIKE);
        $transformer = new SearchTextFilterConditionTransformer();
        $result = $transformer->transform($filterCondition->reveal(), [$field->reveal()]);

        $this->assertInstanceOf(Filter::class, $result);
        $this->assertCount(1, $result);
        $this->assertInstanceOf(FilterCondition::class, $result[0]);
        $this->assertEquals("%$searchPhrase%", $result[0]->getValue());
    }

    public function testTransformDoNotTransformUnsupportedMethod()
    {
        $unsupportedMethod = 'unsupported_method';

        $dataType = $this->prophesize(DataTypeInterface::class);
        $dataType->supports($unsupportedMethod)->willReturn(false);

        $field = $this->prophesize(Field::class);
        $field->getDataType()->willReturn($dataType->reveal());

        $filterCondition = $this->prophesize(FilterCondition::class);
        $filterCondition->getValue()->shouldBeCalled();
        $filterCondition->getMethod()->willReturn($unsupportedMethod);
        $transformer = new SearchTextFilterConditionTransformer();
        $result = $transformer->transform($filterCondition->reveal(), [$field->reveal()]);

        $this->assertInstanceOf(Filter::class, $result);
        $this->assertCount(0, $result);
    }

    public function testTransformOnlyTransformQueryFields()
    {
        $dataType = $this->prophesize(DataTypeInterface::class);
        $dataType->supports(Argument::any())->willReturn(true);

        $field = $this->prophesize(Field::class);
        $field->getDataType()->willReturn($dataType->reveal());
        $field->getDatabaseFilterQueryField()->willReturn(null);

        $filterCondition = $this->prophesize(FilterCondition::class);
        $filterCondition->getValue()->shouldBeCalled();
        $filterCondition->getMethod()->shouldBeCalled();
        $transformer = new SearchTextFilterConditionTransformer();
        $result = $transformer->transform($filterCondition->reveal(), [$field->reveal()]);

        $this->assertInstanceOf(Filter::class, $result);
        $this->assertCount(0, $result);
    }

    public function testTransformOnlyTransformNonArraySelectAliases()
    {
        $dataType = $this->prophesize(DataTypeInterface::class);
        $dataType->supports(Argument::any())->willReturn(true);

        $field = $this->prophesize(Field::class);
        $field->getDataType()->willReturn($dataType->reveal());
        $field->getDatabaseFilterQueryField()->willReturn('query_field');
        $field->getDatabaseSelectAlias()->willReturn(['a', 'b', 'c']);

        $filterCondition = $this->prophesize(FilterCondition::class);
        $filterCondition->getValue()->shouldBeCalled();
        $filterCondition->getMethod()->shouldBeCalled();
        $transformer = new SearchTextFilterConditionTransformer();
        $result = $transformer->transform($filterCondition->reveal(), [$field->reveal()]);

        $this->assertInstanceOf(Filter::class, $result);
        $this->assertCount(0, $result);
    }
}
