<?php

namespace Netdudes\DataSourceryBundle\Tests\DataSource\Util;

use Netdudes\DataSourceryBundle\DataSource\Util\ChoicesBuilder;

class ChoicesBuilderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers Netdudes\DataSourceryBundle\DataSource\Util\ChoicesBuilder::build
     */
    public function testBuildingChoicesWithInvalidConfig()
    {
        $emMock = $this->prepareEntityManagerMock();

        $builder = new ChoicesBuilder($emMock);
        $choices = $builder->build('invalid config');

        $this->assertNull($choices);
    }

    /**
     * @covers Netdudes\DataSourceryBundle\DataSource\Util\ChoicesBuilder::build
     * @covers Netdudes\DataSourceryBundle\DataSource\Util\ChoicesBuilder::getChoicesFromRepositoryForField
     */
    public function testBuildingChoicesFromRepositoryField()
    {
        $fieldName = 'a_field';
        $repositoryName = 'a_test_repository';

        $repositoryChoices = [
            [$fieldName => 'choice 1'],
            [$fieldName => 'choice 2'],
            [$fieldName => 'choice 3'],
        ];

        $queryMock = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')  // because Query is final
            ->disableOriginalConstructor()
            ->getMock();
        $queryMock->expects($this->once())
            ->method('getArrayResult')
            ->willReturn($repositoryChoices);

        $queryBuilderMock = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->setMethods(['select', 'orderBy', 'getQuery'])
            ->getMock();
        $queryBuilderMock->method('select')->will($this->returnSelf());
        $queryBuilderMock->method('orderBy')->will($this->returnSelf());
        $queryBuilderMock->method('getQuery')->willReturn($queryMock);

        $repositoryMock = $this->prepareRepositoryMock();
        $repositoryMock->expects($this->once())
            ->method('createQueryBuilder')
            ->willReturn($queryBuilderMock);

        $emMock = $this->prepareEntityManagerMock();
        $emMock->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo($repositoryName))
            ->willReturn($repositoryMock);

        $builder = new ChoicesBuilder($emMock);
        $choices = $builder->build([
            'repository' => $repositoryName,
            'field' => $fieldName,
        ]);

        $expectedChoices = [
            'choice 1' => 'choice 1',
            'choice 2' => 'choice 2',
            'choice 3' => 'choice 3',
        ];
        $this->assertSame($expectedChoices, $choices);
    }

    /**
     * @covers Netdudes\DataSourceryBundle\DataSource\Util\ChoicesBuilder::build
     * @covers Netdudes\DataSourceryBundle\DataSource\Util\ChoicesBuilder::getChoicesFromRepositoryWithMethod
     */
    public function testBuildingChoicesFromRepositoryMethod()
    {
        $methodName = 'a_method';
        $repositoryName = 'a_test_repository';

        $repositoryChoices = [
            'choice 1',
            'choice 2',
            'choice 3',
        ];

        $repositoryMock = $this->prepareRepositoryMock([$methodName]);
        $repositoryMock->expects($this->once())
            ->method($methodName)
            ->willReturn($repositoryChoices);

        $emMock = $this->prepareEntityManagerMock();
        $emMock->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo($repositoryName))
            ->willReturn($repositoryMock);

        $builder = new ChoicesBuilder($emMock);
        $choices = $builder->build([
            'repository' => $repositoryName,
            'method' => $methodName,
        ]);

        $expectedChoices = [
            'choice 1',
            'choice 2',
            'choice 3',
        ];
        $this->assertSame($expectedChoices, $choices);
    }

    /**
     * @covers Netdudes\DataSourceryBundle\DataSource\Util\ChoicesBuilder::build
     * @covers Netdudes\DataSourceryBundle\DataSource\Util\ChoicesBuilder::getChoicesFromRepositoryWithMethod
     */
    public function testBuildingChoicesFromRepositoryMethodWhenMethodDoesNotExist()
    {
        $methodName = 'a_method';
        $repositoryName = 'a_test_repository';

        $repositoryMock = $this->prepareRepositoryMock([$methodName]);
        $repositoryMock->expects($this->never())
            ->method($methodName);

        $emMock = $this->prepareEntityManagerMock();
        $emMock->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo($repositoryName))
            ->willReturn($repositoryMock);

        $builder = new ChoicesBuilder($emMock);

        $this->setExpectedException('Exception', 'Specified repository does not have \'other_method\' method');
        $builder->build([
            'repository' => $repositoryName,
            'method' => 'other_method',
        ]);
    }

    /**
     * @covers Netdudes\DataSourceryBundle\DataSource\Util\ChoicesBuilder::build
     */
    public function testBuildingChoicesFromRepositoryWithoutSpecifyingFieldOrMethod()
    {
        $repositoryName = 'a_test_repository';

        $repositoryMock = $this->prepareRepositoryMock();

        $emMock = $this->prepareEntityManagerMock();
        $emMock->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo($repositoryName))
            ->willReturn($repositoryMock);

        $builder = new ChoicesBuilder($emMock);

        $this->setExpectedException('Exception', 'Repository source expects field or method parameter');
        $builder->build([
            'repository' => $repositoryName,
        ]);
    }

    /**
     * @covers Netdudes\DataSourceryBundle\DataSource\Util\ChoicesBuilder::build
     * @covers Netdudes\DataSourceryBundle\DataSource\Util\ChoicesBuilder::getChoicesFromCallable
     */
    public function testBuildingChoicesFromCallable()
    {
        $aCallable = function () {
            return ['choice 1', 'choice 2', 'choice 3'];
        };

        $emMock = $this->prepareEntityManagerMock();

        $builder = new ChoicesBuilder($emMock);
        $choices = $builder->build($aCallable);

        $expectedChoices = [
            'choice 1',
            'choice 2',
            'choice 3',
        ];
        $this->assertSame($expectedChoices, $choices);
    }

    /**
     * @covers Netdudes\DataSourceryBundle\DataSource\Util\ChoicesBuilder::build
     * @covers Netdudes\DataSourceryBundle\DataSource\Util\ChoicesBuilder::getChoicesFromCallable
     */
    public function testBuildingChoicesFromCallableWhenResultIsNotAnArray()
    {
        $aCallable = function () {
            return 'choice';
        };

        $emMock = $this->prepareEntityManagerMock();

        $builder = new ChoicesBuilder($emMock);

        $this->setExpectedException('Exception', 'Choices callback defined in table configurations must return arrays');
        $builder->build($aCallable);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function prepareEntityManagerMock()
    {
        $mock = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(['getRepository'])
            ->getMock();

        return $mock;
    }

    /**
     * @param array $extraMethods
     *
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    private function prepareRepositoryMock(array $extraMethods = [])
    {
        $mock = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->setMethods(array_merge(['createQueryBuilder'], $extraMethods))
            ->disableOriginalConstructor()
            ->getMock();

        return $mock;
    }
}
