# Netdudes\DataSourceryBundle
[![Build Status](https://travis-ci.org/netdudes/DataSourceryBundle.svg?branch=master)](https://travis-ci.org/netdudes/DataSourceryBundle)
[![Code Climate](https://codeclimate.com/github/netdudes/DataSourceryBundle/badges/gpa.svg)](https://codeclimate.com/github/netdudes/DataSourceryBundle)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/5b739d71-fe46-468f-b1b0-667af8411a1c/mini.png)](https://insight.sensiolabs.com/projects/5b739d71-fe46-468f-b1b0-667af8411a1c)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/netdudes/DataSourceryBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/netdudes/DataSourceryBundle/?branch=master)

[![Latest Stable Version](https://poser.pugx.org/netdudes/data-sourcery-bundle/v/stable.svg)](https://packagist.org/packages/netdudes/data-sourcery-bundle) [![Total Downloads](https://poser.pugx.org/netdudes/data-sourcery-bundle/downloads.svg)](https://packagist.org/packages/netdudes/data-sourcery-bundle) [![Latest Unstable Version](https://poser.pugx.org/netdudes/data-sourcery-bundle/v/unstable.svg)](https://packagist.org/packages/netdudes/data-sourcery-bundle) [![License](https://poser.pugx.org/netdudes/data-sourcery-bundle/license.svg)](https://packagist.org/packages/netdudes/data-sourcery-bundle)

DataSourceryBundle is a neat tool to handle building and performing complex queries on data sets, including support for natural-language queries and safe handling of user provided query parameters.

## Usage *(work in progress!)*

Assume we have one entity in our system managed by **Doctrine**, called `User`, that looks like this:

```
User {
	string username
	string nameFirst
	string nameLast
	\DateTime registered
	User bestFriend => OtM with another user
	User worstEnemy => OtM with another user
}
```

You can get the building block of the library, the `DataSource`, from a builder. From here and now on we will assume you have a DI container (e.g. Symfony) where the needed services are registered.


```php
$dataSourceBuilder = $container
    ->get('netdudes.data_query.data_source.factory')
    ->createBuilder('My\Entities\User');
```

With a builder is easy to create a `Datasource`


```php
$dataSourceBuilder
    ->addField('username', 'string', 'username')
    ->addField('bestFriendUsername' 'string', 'bestFriend.username')
    ->addField('worstEnemyUsername', 'string', 'worstEnemy.username')
    ->addField('friendOfMyEnemyUsername', 'string', 'worstEnemy.bestFriend.username')
    ->addField('registered', 'date', 'registered');

$dataSource = $dataSourceBuilder->build();
```

Alternatively, a data source can be generated from a configuration class, very similarly to how Symfony Forms are built.

```php
class MyNiceDataSourceConfig implements DataSourceConfigurationInterface
{

    public function getEntityClass()
    {
        return 'My\Entities\User';
    }

    public function buildDataSource(DataSourceBuilderInterface $builder)
    {
        $builder
            ->addField('username', 'string', 'username')
            ->addField('bestFriendUsername', 'string', 'bestFriend.username')
            ->addField('worstEnemyUsername', 'string', 'worstEnemy.username')
            ->addField('friendOfMyEnemyUsername', 'string', 'worstEnemy.bestFriend.username')
            ->addField('registered', 'date', 'registered');
    }
}

$dataSource = $container
	->get('netdudes.data_query.data_source.factory')
	->createFromConfiguration(new MyNiceDataSourceConfig());
```

In order to query you data source, you must have a `Query` object. Creating one manually is easy:

```php
$query = new Query();
$query->setSelect(['username', 'bestFriendUsername', 'worstEnemyUsername', 'friendOfMyEnemyUsername', 'registered']);

$filter = new Filter(
    [
        new FilterCondition('username', FilterCondition::METHOD_STRING_EQ, 'admin')
    ]
);

$query->setFilter($filter);
```

Alternatively you can use the built in parser for the system's language, UQL:

```php
$uqlInterpreter = $container->get('netdudes_table.uql.interpreter.factory')->create($dataSource);
$filter = $uqlInterpreter->generateFilters('username != "admin"');

$query->setFilter($filter);
```

Finally, you can get your data from the data source

```php
$data = $dataSource->getData($query);

dump($data);
```

Giving

```php
array:1 [
  0 => array:5 [
    "username" => "admin"
    "bestFriendUsername" => "Max"
    "worstEnemyUsername" => "John"
    "friendOfMyEnemyUsername" => "Max"
    "registered" => DateTime {#124
      +"date": "2014-02-01 00:00:00.000000"
      +"timezone_type": 3
      +"timezone": "Europe/Berlin"
    }
  ]
]
```
