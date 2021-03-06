<?php

namespace Netdudes\DataSourceryBundle\Tests\DataSource\Util;

use Netdudes\DataSourceryBundle\DataSource\Util\ChoicesBuilder;
use PHPUnit\Framework\TestCase;

class ChoicesBuilderTest extends TestCase
{
    public function testBuildingChoicesWithInvalidConfig()
    {
        $invalidConfig = 'invalid configuration';

        $emMock = $this->prepareEntityManagerMock();

        $builder = new ChoicesBuilder($emMock);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No usable configuration was found');
        $builder->build($invalidConfig);
    }

    public function testBuildingChoicesFromRepositoryField()
    {
        $fieldName = 'a_field';
        $repositoryName = 'a_test_repository';

        $repositoryChoices = [
            [$fieldName => 'choice 1'],
            [$fieldName => 'choice 2'],
            [$fieldName => 'choice 3'],
        ];

        $queryMock = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')// because Query is final
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
        $choices = $builder->build(
            [
                'repository' => $repositoryName,
                'field' => $fieldName,
            ]
        );

        $expectedChoices = [
            'choice 1' => 'choice 1',
            'choice 2' => 'choice 2',
            'choice 3' => 'choice 3',
        ];
        $this->assertSame($expectedChoices, $choices);
    }

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
        $choices = $builder->build(
            [
                'repository' => $repositoryName,
                'method' => $methodName,
            ]
        );

        $expectedChoices = [
            'choice 1',
            'choice 2',
            'choice 3',
        ];
        $this->assertSame($expectedChoices, $choices);
    }

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

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Specified repository does not have 'other_method' method");
        $builder->build(
            [
                'repository' => $repositoryName,
                'method' => 'other_method',
            ]
        );
    }

    public function testBuildingChoicesFromRepositoryMethodWhenMethodDoesNotReturnAnArray()
    {
        $methodName = 'a_method';
        $repositoryName = 'a_test_repository';

        $repositoryMock = $this->prepareRepositoryMock([$methodName]);
        $repositoryMock->expects($this->once())
            ->method($methodName)
            ->willReturn('not an array');

        $emMock = $this->prepareEntityManagerMock();
        $emMock->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo($repositoryName))
            ->willReturn($repositoryMock);

        $builder = new ChoicesBuilder($emMock);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage("Repository method $methodName must return an array of choices");
        $builder->build(
            [
                'repository' => $repositoryName,
                'method' => $methodName,
            ]
        );
    }

    public function testBuildingChoicesFromRepositoryWhenSpecifyingBothFieldAndMethod()
    {
        $repositoryName = 'a_test_repository';

        $repositoryMock = $this->prepareRepositoryMock();

        $emMock = $this->prepareEntityManagerMock();
        $emMock->expects($this->once())
            ->method('getRepository')
            ->with($this->equalTo($repositoryName))
            ->willReturn($repositoryMock);

        $builder = new ChoicesBuilder($emMock);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Repository source expects field or method parameter, but not both');
        $builder->build(
            [
                'repository' => $repositoryName,
                'field' => 'a_field',
                'method' => 'a_method',
            ]
        );
    }

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

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Repository source expects field or method parameter');
        $builder->build(
            [
                'repository' => $repositoryName,
            ]
        );
    }

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

    public function testBuildingChoicesFromCallableWhenResultIsNotAnArray()
    {
        $aCallable = function () {
            return 'not an array';
        };

        $emMock = $this->prepareEntityManagerMock();

        $builder = new ChoicesBuilder($emMock);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The provided choice callback must return an array of choices');
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
