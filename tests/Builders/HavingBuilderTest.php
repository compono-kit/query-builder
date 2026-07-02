<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Tests\Builders;

use ComponoKit\Databases\Sql\QueryBuilder\Builders\HavingBuilder;
use ComponoKit\Databases\Sql\QueryBuilder\Criterias\Conditions\Condition;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Column;
use ComponoKit\Databases\Sql\QueryBuilder\Models\ColumnName;
use ComponoKit\Databases\Sql\QueryBuilder\Models\ComparisonValue;
use ComponoKit\Databases\Sql\QueryBuilder\Models\TableName;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Types\ComparisonOperator;
use PHPUnit\Framework\TestCase;

class HavingBuilderTest extends TestCase
{
	public function testEmptyCriteriasReturnsEmptyString(): void
	{
		$this->assertSame('', HavingBuilder::build([]));
	}

	public function testSingleCondition(): void
	{
		$column    = new Column(new TableName(''), new ColumnName('total'));
		$condition = new Condition($column, ComparisonOperator::greaterOperator(), null, new ComparisonValue(5));

		$this->assertSame(' HAVING total > 5', HavingBuilder::build([$condition]));
	}

	public function testMultipleConditionsJoinedWithAnd(): void
	{
		$column1     = new Column(new TableName(''), new ColumnName('total'));
		$condition1  = new Condition($column1, ComparisonOperator::greaterOperator(), null, new ComparisonValue(5));
		$column2     = new Column(new TableName(''), new ColumnName('count'));
		$condition2  = new Condition($column2, ComparisonOperator::lessOperator(), null, new ComparisonValue(100));

		$result = HavingBuilder::build([$condition1, $condition2]);

		$this->assertSame(' HAVING total > 5 AND count < 100', $result);
	}
}
