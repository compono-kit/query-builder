<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Tests\Builders;

use ComponoKit\Databases\Sql\QueryBuilder\Builders\GroupByBuilder;
use ComponoKit\Databases\Sql\QueryBuilder\Models\Column;
use ComponoKit\Databases\Sql\QueryBuilder\Models\ColumnName;
use ComponoKit\Databases\Sql\QueryBuilder\Models\TableName;
use PHPUnit\Framework\TestCase;

class GroupByBuilderTest extends TestCase
{
	public function testEmptyColumnsReturnEmptyString(): void
	{
		$this->assertSame('', ( new GroupByBuilder([]) )->build());
	}

	public function testBuildsFromColumns(): void
	{
		$column = new Column(new TableName(''), new ColumnName('department'));

		$this->assertSame(' GROUP BY department', ( new GroupByBuilder([$column]) )->build());
	}

	public function testFromSqlParsesClause(): void
	{
		$output = GroupByBuilder::fromSql('department, u.id')->build();

		$this->assertStringContainsString('GROUP BY', $output);
		$this->assertStringContainsString('department', $output);
	}
}
