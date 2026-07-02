<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Tests\Builders;

use ComponoKit\Databases\Sql\QueryBuilder\Builders\JoinBuilder;
use ComponoKit\Databases\Sql\QueryBuilder\Criterias\Conditions\Condition;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Column;
use ComponoKit\Databases\Sql\QueryBuilder\Models\ColumnName;
use ComponoKit\Databases\Sql\QueryBuilder\Models\ComparisonColumn;
use ComponoKit\Databases\Sql\QueryBuilder\Models\JoinClause;
use ComponoKit\Databases\Sql\QueryBuilder\Models\TableName;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Types\ComparisonOperator;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Types\JoinType;
use PHPUnit\Framework\TestCase;

class JoinBuilderTest extends TestCase
{
	public function testEmptyJoinClausesReturnsEmptyString(): void
	{
		$this->assertSame('', JoinBuilder::build([]));
	}

	public function testInnerJoinWithOnCondition(): void
	{
		$onCondition = new Condition(
			new Column(new TableName('u'), new ColumnName('id')),
			ComparisonOperator::equalOperator(),
			null,
			new ComparisonColumn(new Column(new TableName('o'), new ColumnName('user_id')))
		);

		$joinClause = new JoinClause(new TableName('orders', 'o'), JoinType::INNER, $onCondition);
		$output     = JoinBuilder::build([$joinClause]);

		$this->assertStringContainsString('INNER JOIN', $output);
		$this->assertStringContainsString('orders', $output);
		$this->assertStringContainsString(' o ', $output);
		$this->assertStringContainsString('ON', $output);
	}

	public function testLeftJoinWithOnCondition(): void
	{
		$onCondition = new Condition(
			new Column(new TableName(''), new ColumnName('id')),
			ComparisonOperator::equalOperator(),
			null,
			new ComparisonColumn(new Column(new TableName(''), new ColumnName('user_id')))
		);

		$joinClause = new JoinClause(new TableName('orders'), JoinType::LEFT, $onCondition);
		$output     = JoinBuilder::build([$joinClause]);

		$this->assertStringContainsString('LEFT JOIN', $output);
	}

	public function testRightJoinWithOnCondition(): void
	{
		$joinClause = new JoinClause(new TableName('categories'), JoinType::RIGHT, null);
		$output     = JoinBuilder::build([$joinClause]);

		$this->assertStringContainsString('RIGHT JOIN', $output);
	}

	public function testCrossJoinWithoutOnCondition(): void
	{
		$joinClause = new JoinClause(new TableName('sizes'), JoinType::CROSS, null);
		$output     = JoinBuilder::build([$joinClause]);

		$this->assertStringContainsString('CROSS JOIN', $output);
		$this->assertStringNotContainsString('ON', $output);
	}

	public function testMultipleJoinsAreChained(): void
	{
		$joinClauseOne = new JoinClause(new TableName('orders'), JoinType::INNER, null);
		$joinClauseTwo = new JoinClause(new TableName('products'), JoinType::LEFT, null);
		$output        = JoinBuilder::build([$joinClauseOne, $joinClauseTwo]);

		$this->assertStringContainsString('INNER JOIN', $output);
		$this->assertStringContainsString('LEFT JOIN', $output);
	}

	public function testJoinTableWithoutAlias(): void
	{
		$joinClause = new JoinClause(new TableName('orders'), JoinType::INNER, null);
		$output     = JoinBuilder::build([$joinClause]);

		$this->assertSame(' INNER JOIN orders', $output);
	}

	public function testTableNameWithHyphenIsQuoted(): void
	{
		$joinClause = new JoinClause(new TableName('delivery-orders'), JoinType::INNER, null);
		$output     = JoinBuilder::build([$joinClause]);

		$this->assertStringContainsString('`delivery-orders`', $output);
	}
}
