<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Tests\Factories;

use ComponoKit\Databases\Sql\QueryBuilder\Factories\SqlToQueryBuilderFactory;
use PHPUnit\Framework\TestCase;

class SqlToQueryBuilderFactoryJoinTest extends TestCase
{
	public function testSingleInnerJoin(): void
	{
		$parsed = SqlToQueryBuilderFactory::fromSql('SELECT * FROM users u INNER JOIN orders o ON u.id = o.user_id');
		$output = $parsed->buildAll();

		$this->assertStringContainsString('INNER JOIN', $output);
		$this->assertStringContainsString('`orders`', $output);
	}

	public function testJoinComesBeforeWhere(): void
	{
		$parsed = SqlToQueryBuilderFactory::fromSql('SELECT * FROM users u INNER JOIN orders o ON u.id = o.user_id WHERE u.active = 1');
		$output = $parsed->buildAll();

		$this->assertLessThan(strpos($output, 'WHERE'), strpos($output, 'JOIN'));
	}

	public function testMultipleJoins(): void
	{
		$sql    = 'SELECT * FROM users u INNER JOIN orders o ON u.id = o.user_id LEFT JOIN products p ON o.product_id = p.id';
		$parsed = SqlToQueryBuilderFactory::fromSql($sql);
		$output = $parsed->buildAll();

		$this->assertStringContainsString('INNER JOIN', $output);
		$this->assertStringContainsString('LEFT JOIN', $output);
	}

	public function testJoinWithoutWhereClause(): void
	{
		$parsed = SqlToQueryBuilderFactory::fromSql('SELECT * FROM users u INNER JOIN orders o ON u.id = o.user_id');
		$output = $parsed->buildAll();

		$this->assertStringContainsString('JOIN', $output);
		$this->assertStringContainsString('WHERE 1', $output);
	}

	public function testJoinCombinedWithGroupByAndOrderBy(): void
	{
		$sql    = 'SELECT * FROM users u INNER JOIN orders o ON u.id = o.user_id GROUP BY u.id ORDER BY u.name ASC';
		$parsed = SqlToQueryBuilderFactory::fromSql($sql);
		$output = $parsed->buildAll();

		$joinPosition    = strpos($output, 'JOIN');
		$groupByPosition = strpos($output, 'GROUP BY');
		$orderByPosition = strpos($output, 'ORDER BY');

		$this->assertLessThan($groupByPosition, $joinPosition);
		$this->assertLessThan($orderByPosition, $groupByPosition);
	}
}
