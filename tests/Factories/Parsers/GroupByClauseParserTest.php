<?php declare(strict_types=1);

namespace ComponoKit\Databases\Sql\QueryBuilder\Tests\Factories\Parsers;

use ComponoKit\Databases\Sql\QueryBuilder\Factories\Exceptions\SqlParseException;
use ComponoKit\Databases\Sql\QueryBuilder\Factories\Parsers\GroupByClauseParser;
use PHPUnit\Framework\TestCase;

class GroupByClauseParserTest extends TestCase
{
	public function testSingleColumn(): void
	{
		$columns = GroupByClauseParser::parse('department');

		$this->assertCount(1, $columns);
		$this->assertSame('department', $columns[0]->getColumnName()->getPureName());
	}

	public function testTablePrefixedColumn(): void
	{
		$columns = GroupByClauseParser::parse('users.department');

		$this->assertSame('users', $columns[0]->getTableName()->getName());
		$this->assertSame('department', $columns[0]->getColumnName()->getPureName());
	}

	public function testMultipleColumns(): void
	{
		$columns = GroupByClauseParser::parse('department, role');

		$this->assertCount(2, $columns);
		$this->assertSame('department', $columns[0]->getColumnName()->getPureName());
		$this->assertSame('role', $columns[1]->getColumnName()->getPureName());
	}

	public function testBacktickedIdentifiers(): void
	{
		$columns = GroupByClauseParser::parse('`users`.`department`');

		$this->assertSame('users', $columns[0]->getTableName()->getName());
		$this->assertSame('department', $columns[0]->getColumnName()->getPureName());
	}

	public function testInvalidColumnThrows(): void
	{
		$this->expectException(SqlParseException::class);
		GroupByClauseParser::parse('123invalid');
	}
}
