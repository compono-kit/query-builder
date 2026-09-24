# QueryBuilder

A PHP 8.1+ library that builds SQL `SELECT` and `UPDATE` statements from objects, and parses existing SQL strings into those objects.

## What is it useful for?

* **Building queries dynamically:** add filters, joins, sorting and limits step by step, e.g. from a search form or an API filter, without concatenating strings yourself.
* **Prepared statements by default:** values can be passed as named parameters (`:name`), and the matching parameter array is collected for you.
* **Reusing one set of filters:** the same `QueryBuilder` (WHERE, JOIN, ORDER BY, LIMIT) can produce a `SELECT` or an `UPDATE`.
* **Modifying existing SQL:** parse a SQL string, add more conditions, and render it again.
* **Validating against a schema:** check that tables and columns in a query actually exist.

All builders are **immutable**: every `add…`/`use…` method returns a new instance.

## Installation

```bash
composer require compono-kit/query-builder
```

## Concept

| Class | Responsibility |
|---|---|
| `QueryBuilder` | Holds the filters: `addCriteria()` (WHERE), `addJoinClause()`, `addOrderBy()`, `addGroupByColumn()`, `addHavingCriteria()`, `useLimit()` |
| `SelectBuilder` | Statement type SELECT: `useTable()`, `useSelectExpressions()` |
| `UpdateBuilder` | Statement type UPDATE: `useTable()`, `addConditionValue()` (SET part) |

Both statement builders implement `BuildsStatements`. You pass one of them to `QueryBuilder::buildAll()` to get the SQL string, and to `QueryBuilder::getStatementPreparedParams()` to get the matching parameters.

The `add…` methods are variadic: pass one or more objects, or spread an array with `...$array`.

## Examples

The examples use these imports:

```php
use ComponoKit\Databases\Sql\QueryBuilder\Builders\QueryBuilder;
use ComponoKit\Databases\Sql\QueryBuilder\Builders\SelectBuilder;
use ComponoKit\Databases\Sql\QueryBuilder\Builders\UpdateBuilder;
use ComponoKit\Databases\Sql\QueryBuilder\Criterias\Conditions\EqualCondition;
use ComponoKit\Databases\Sql\QueryBuilder\Criterias\Conditions\GreaterCondition;
use ComponoKit\Databases\Sql\QueryBuilder\Criterias\IsolatedCriteria;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Column;
use ComponoKit\Databases\Sql\QueryBuilder\Models\ColumnName;
use ComponoKit\Databases\Sql\QueryBuilder\Models\ComparisonValue;
use ComponoKit\Databases\Sql\QueryBuilder\Models\ComparisonValueCondition;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Limit;
use ComponoKit\Databases\Sql\QueryBuilder\Models\OrderBy;
use ComponoKit\Databases\Sql\QueryBuilder\Models\PreparedParameter;
use ComponoKit\Databases\Sql\QueryBuilder\Models\PreparedParameterCondition;
use ComponoKit\Databases\Sql\QueryBuilder\Models\TableName;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Types\LogicalOperator;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Types\OrderDirection;
```

### SELECT

```php
$statusColumn = new Column( new TableName( 'u' ), new ColumnName( 'status' ) );
$nameColumn   = new Column( new TableName( 'u' ), new ColumnName( 'name' ) );

$queryBuilder = ( new QueryBuilder() )
	->addCriteria( new EqualCondition( new PreparedParameterCondition( $statusColumn, new PreparedParameter( 'status', 'active' ) ) ) )
	->addOrderBy( new OrderBy( $nameColumn, OrderDirection::ASC ) )
	->useLimit( new Limit( 10 ) );

$selectBuilder = ( new SelectBuilder() )
	->useTable( 'users', 'u' )
	->useSelectExpressions( 'u.id', 'u.name' );

$sql    = $queryBuilder->buildAll( $selectBuilder );
$params = $queryBuilder->getStatementPreparedParams( $selectBuilder );
```

```sql
SELECT u.id, u.name FROM users u WHERE u.status = :status ORDER BY u.name ASC LIMIT 10
```

`$params` is `['status' => 'active']`.

### UPDATE

`addConditionValue()` fills the `SET` part. A value can be a prepared parameter (`PreparedParameterCondition`) or a literal (`ComparisonValueCondition`).

```php
$idColumn     = new Column( new TableName( '' ), new ColumnName( 'id' ) );
$nameColumn   = new Column( new TableName( '' ), new ColumnName( 'name' ) );
$statusColumn = new Column( new TableName( '' ), new ColumnName( 'status' ) );

$queryBuilder = ( new QueryBuilder() )
	->addCriteria( new EqualCondition( new PreparedParameterCondition( $idColumn, new PreparedParameter( 'id', '5' ) ) ) );

$updateBuilder = ( new UpdateBuilder() )
	->useTable( 'users' )
	->addConditionValue(
		new PreparedParameterCondition( $nameColumn, new PreparedParameter( 'name', 'Max' ) ),
		new ComparisonValueCondition( $statusColumn, new ComparisonValue( 'active' ) )
	);

$sql    = $queryBuilder->buildAll( $updateBuilder );
$params = $queryBuilder->getStatementPreparedParams( $updateBuilder );
```

```sql
UPDATE users SET name = :name, status = 'active' WHERE id = :id
```

`$params` is `['name' => 'Max', 'id' => '5']`.

Notes for UPDATE:

* Without criteria, no `WHERE` is rendered, so the statement changes **every row** of the table.
* JOIN, ORDER BY and LIMIT are supported. GROUP BY and HAVING throw a `LogicException`.
* If the same parameter name is used in SET and WHERE with different values, `getStatementPreparedParams()` throws a `LogicException`.

### Reusing filters for SELECT and UPDATE

```php
$queryBuilder = ( new QueryBuilder() )
	->addCriteria( new GreaterCondition( new ComparisonValueCondition( $idColumn, new ComparisonValue( 100 ) ) ) );

$queryBuilder->buildAll( ( new SelectBuilder() )->useTable( 'users' ) );
$queryBuilder->buildAll( $updateBuilder );
```

```sql
SELECT * FROM users WHERE id > 100
UPDATE users SET name = :name, status = 'active' WHERE id > 100
```

### Grouping conditions with AND / OR

Multiple criteria in `addCriteria()` are joined with `AND`. Use `IsolatedCriteria` for a group in parentheses:

```php
$queryBuilder = ( new QueryBuilder() )->addCriteria(
	new IsolatedCriteria(
		LogicalOperator::OR,
		new EqualCondition( new ComparisonValueCondition( $statusColumn, new ComparisonValue( 'active' ) ) ),
		new EqualCondition( new ComparisonValueCondition( $statusColumn, new ComparisonValue( 'pending' ) ) )
	)
);
```

```sql
... WHERE (status = 'active' OR status = 'pending')
```

### Parsing existing SQL

`SqlToQueryBuilderFactory` turns a SQL string into a `QueryBuilder` and a `SelectBuilder`, which you can extend and render again:

```php
use ComponoKit\Databases\Sql\QueryBuilder\Factories\SqlToQueryBuilderFactory;

$factory = new SqlToQueryBuilderFactory( 'SELECT id, name FROM users u WHERE u.id = :id ORDER BY name DESC LIMIT 5' );

$queryBuilder  = $factory->buildQueryBuilder()->addCriteria( $additionalCriteria );
$selectBuilder = $factory->buildSelectBuilder();

$sql    = $queryBuilder->buildAll( $selectBuilder );
$params = $queryBuilder->getStatementPreparedParams( $selectBuilder );
```

### Validating against a schema

```php
use ComponoKit\Databases\Sql\QueryBuilder\Builders\QueryValidator;
use ComponoKit\Databases\Sql\QueryBuilder\Schema\SchemaParser;

$schemaRegistry = SchemaParser::fromFile( __DIR__ . '/schema.sql' );

QueryValidator::validate( $selectBuilder, $queryBuilder, $schemaRegistry );
```

`validate()` throws a `ValidationException` if a table or column in the SELECT or JOIN is not in the schema.

## Development

See [DOCKER.md](DOCKER.md) for the Docker setup.
