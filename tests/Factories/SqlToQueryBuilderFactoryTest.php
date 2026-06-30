<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Tests\Factories;

use ComponoKit\Databases\Sql\QueryBuilder\Factories\SqlToQueryBuilderFactory;
use PHPUnit\Framework\TestCase;

class SqlToQueryBuilderFactoryTest extends TestCase
{
	public function testSimpleWhereClause(): void
	{
		$parsed = SqlToQueryBuilderFactory::fromSql("SELECT * FROM users WHERE id = 1");

		$this->assertStringContainsString('WHERE', $parsed->buildAll());
		$this->assertStringContainsString('`id` = 1', $parsed->buildAll());
	}

	public function testOrderBy(): void
	{
		$parsed = SqlToQueryBuilderFactory::fromSql("SELECT * FROM users ORDER BY created_at DESC");

		$this->assertStringContainsString('ORDER BY', $parsed->buildAll());
		$this->assertStringContainsString('DESC', $parsed->buildAll());
	}

	public function testLimit(): void
	{
		$parsed = SqlToQueryBuilderFactory::fromSql("SELECT * FROM users LIMIT 10");

		$this->assertStringContainsString('LIMIT 10', $parsed->buildAll());
	}

	public function testLimitWithOffset(): void
	{
		$parsed = SqlToQueryBuilderFactory::fromSql("SELECT * FROM users LIMIT 10 OFFSET 5");

		$output = $parsed->buildAll();
		$this->assertStringContainsString('LIMIT', $output);
		$this->assertStringContainsString('10', $output);
	}

	public function testGroupBy(): void
	{
		$parsed = SqlToQueryBuilderFactory::fromSql("SELECT * FROM users GROUP BY department");
		$output = $parsed->buildAll();

		$this->assertStringContainsString('GROUP BY', $output);
		$this->assertMatchesRegularExpression('/GROUP BY\s+`department`/', $output);
	}

	public function testFullQuery(): void
	{
		$sql    = "SELECT * FROM users WHERE id > 0 AND status = 'active' ORDER BY name ASC LIMIT 20 OFFSET 10";
		$parsed = SqlToQueryBuilderFactory::fromSql($sql);
		$output = $parsed->buildAll();

		$this->assertStringContainsString('WHERE', $output);
		$this->assertStringContainsString('ORDER BY', $output);
		$this->assertStringContainsString('LIMIT', $output);
	}

	public function testQueryWithoutAnyClause(): void
	{
		$parsed = SqlToQueryBuilderFactory::fromSql("SELECT * FROM users");
		$output = $parsed->buildAll();

		$this->assertSame(' WHERE 1', $output);
	}

	public function testPreparedParameters(): void
	{
		$parsed = SqlToQueryBuilderFactory::fromSql("SELECT * FROM users WHERE id = :id AND name = :name");
		$params = $parsed->getQueryBuilder()->getPreparedParams();

		$this->assertArrayHasKey('id', $params);
		$this->assertArrayHasKey('name', $params);
	}

	public function testHavingClause(): void
	{
		$parsed = SqlToQueryBuilderFactory::fromSql("SELECT * FROM users GROUP BY department HAVING count > 5");
		$output = $parsed->buildAll();

		$this->assertStringContainsString('HAVING', $output);
		$this->assertStringContainsString('`count` > 5', $output);
	}

	public function testHavingComesAfterGroupBy(): void
	{
		$parsed = SqlToQueryBuilderFactory::fromSql("SELECT * FROM users GROUP BY dept HAVING total > 10 ORDER BY dept");
		$output = $parsed->buildAll();

		$groupByPos = strpos($output, 'GROUP BY');
		$havingPos  = strpos($output, 'HAVING');
		$orderByPos = strpos($output, 'ORDER BY');

		$this->assertLessThan($havingPos, $groupByPos);
		$this->assertLessThan($orderByPos, $havingPos);
	}

	public function testBuildAllAfterAddingMoreCriteria(): void
	{
		$parsed  = SqlToQueryBuilderFactory::fromSql("SELECT * FROM users WHERE id = 1");
		$builder = $parsed->getQueryBuilder();

		$output = $builder->buildWhereStatement();
		$this->assertStringContainsString('`id` = 1', $output);
	}
}
